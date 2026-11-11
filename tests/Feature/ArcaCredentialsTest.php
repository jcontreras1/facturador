<?php

use App\Http\Controllers\AfipWS\Afip;
use App\Models\User;
use App\Models\VariableGlobal;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->withoutVite();
    $this->arcaBasePath = storage_path('framework/testing/arca-'.Str::uuid());
    File::makeDirectory($this->arcaBasePath.DIRECTORY_SEPARATOR.'afip_resources', 0755, true);
    File::makeDirectory($this->arcaBasePath.DIRECTORY_SEPARATOR.'afip_ws', 0755, true);
    File::copy(base_path('afip_ws/wsaa.wsdl'), $this->arcaBasePath.DIRECTORY_SEPARATOR.'afip_ws'.DIRECTORY_SEPARATOR.'wsaa.wsdl');

    $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    $csr = openssl_csr_new(['commonName' => 'ARCA test'], $key, ['digest_alg' => 'sha256']);
    $certificate = openssl_csr_sign($csr, null, $key, 30, ['digest_alg' => 'sha256']);
    openssl_pkey_export($key, $this->arcaPrivateKey);
    openssl_x509_export($certificate, $this->arcaCertificate);
    File::put($this->arcaBasePath.DIRECTORY_SEPARATOR.'afip_resources'.DIRECTORY_SEPARATOR.'key', $this->arcaPrivateKey);
    File::put($this->arcaBasePath.DIRECTORY_SEPARATOR.'afip_resources'.DIRECTORY_SEPARATOR.'cert', $this->arcaCertificate);

    foreach (['CUIT_EMPRESA' => '20123456789', 'AFIP_KEY' => 'obsolete database key', 'AFIP_CERTIFICADO' => 'obsolete database certificate', 'VENCIMIENTO_CERTIFICADO' => '2000-01-01'] as $name => $value) {
        VariableGlobal::forceCreate(['clave' => $name, 'descripcion' => $name, 'valor' => $value]);
    }

    $this->withArcaFiles = function (Closure $callback) {
        $originalBasePath = $this->app->basePath();
        $this->app->setBasePath($this->arcaBasePath);

        try {
            return $callback();
        } finally {
            $this->app->setBasePath($originalBasePath);
        }
    };

    $this->arcaSigner = function () {
        return new class extends Afip
        {
            public function sign(string $inputPath, string $outputPath): void
            {
                $this->SignTicket($inputPath, $outputPath);
            }
        };
    };
});

afterEach(function () {
    File::deleteDirectory($this->arcaBasePath);
});

test('ARCA signs and verifies a ticket using only the local certificate and private key', function () {
    ($this->withArcaFiles)(function () {
        $afip = ($this->arcaSigner)();
        $input = afipDir().'ticket.xml';
        $output = afipDir().'ticket.p7m';
        $verified = afipDir().'verified.xml';
        $ticket = '<loginTicketRequest><service>wsfe</service></loginTicketRequest>';
        File::put($input, $ticket);

        $afip->sign($input, $output);

        expect(openssl_pkcs7_verify($output, PKCS7_NOVERIFY, null, [], null, $verified))->toBeTrue();
        expect(File::get($verified))->toBe($ticket);
        expect($afip->CERT)->toBe(afipDir().'cert');
        expect($afip->PRIVATEKEY)->toBe(afipDir().'key');
    });
});

test('ARCA rejects a certificate that does not match the local private key before contacting WSAA', function () {
    ($this->withArcaFiles)(function () {
        $differentKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($differentKey, $privateKey);
        File::put(afipDir().'key', $privateKey);

        expect(fn () => (new Afip)->GetServiceTA('wsfe'))
            ->toThrow(Exception::class, 'no corresponde a la clave privada');
    });
});

test('ARCA does not fall back to database credentials when a local file is missing', function (string $file) {
    ($this->withArcaFiles)(function () use ($file) {
        File::delete(afipDir().$file);

        expect(fn () => new Afip)->toThrow(Exception::class, afipDir().$file);
    });
})->with(['cert', 'key']);

test('ARCA reports invalid local PEM credentials explicitly', function (string $file, string $message) {
    ($this->withArcaFiles)(function () use ($file, $message) {
        File::put(afipDir().$file, 'invalid PEM');

        expect(fn () => ($this->arcaSigner)()->sign(afipDir().'input.xml', afipDir().'output.tmp'))
            ->toThrow(Exception::class, $message);
    });
})->with([
    ['cert', 'no contiene un certificado PEM valido'],
    ['key', 'No se pudo leer la clave privada PEM'],
]);

test('ARCA reports PKCS7 signing failures instead of returning an empty authorization', function () {
    ($this->withArcaFiles)(function () {
        expect(fn () => ($this->arcaSigner)()->sign(afipDir().'missing.xml', afipDir().'output.tmp'))
            ->toThrow(Exception::class, 'No se pudo firmar el ticket de acceso a ARCA');
    });
});

test('CSR generation uses the local key instead of the obsolete database key', function () {
    $this->actingAs(User::factory()->admin()->create());
    VariableGlobal::forceCreate(['clave' => 'RAZON_SOCIAL', 'descripcion' => 'Razon social', 'valor' => 'ARCA Test']);

    ($this->withArcaFiles)(function () {
        $response = $this->get(route('afip.makeCSR'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pkcs10');

        $csrKey = openssl_pkey_get_details(openssl_csr_get_public_key($response->getContent()));
        $fileKey = openssl_pkey_get_details(openssl_pkey_get_private($this->arcaPrivateKey));
        expect($csrKey['key'])->toBe($fileKey['key']);
    });
});

test('configuration reads credential availability and expiration from local files', function () {
    $this->actingAs(User::factory()->admin()->create());
    VariableGlobal::whereIn('clave', ['AFIP_KEY', 'AFIP_CERTIFICADO'])->update(['valor' => '']);

    ($this->withArcaFiles)(function () {
        $response = $this->get(route('config.index'));
        $response->assertOk()
            ->assertViewHas('afipKeyMissing', false)
            ->assertViewHas('afipCertificateMissing', false)
            ->assertViewHas('afipCertificateInvalid', false)
            ->assertViewHas('afipCredentialsMismatch', false);

        $certificateData = openssl_x509_parse($this->arcaCertificate);
        expect($response->viewData('afipCertificateExpiration')->timestamp)->toBe($certificateData['validTo_time_t']);
        $response->assertSee('Certificado cargado.');
    });
});

test('configuration does not treat obsolete database credentials as installed files', function () {
    $this->actingAs(User::factory()->admin()->create());

    ($this->withArcaFiles)(function () {
        File::delete([afipDir().'cert', afipDir().'key']);

        $this->get(route('config.index'))->assertOk()
            ->assertViewHas('afipKeyMissing', true)
            ->assertViewHas('afipCertificateMissing', true)
            ->assertSee('No hay certificado cargado.');
    });
});

test('configuration warns when local credentials do not match', function () {
    $this->actingAs(User::factory()->admin()->create());

    ($this->withArcaFiles)(function () {
        $differentKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($differentKey, $privateKey);
        File::put(afipDir().'key', $privateKey);

        $this->get(route('config.index'))->assertOk()
            ->assertViewHas('afipCredentialsMismatch', true)
            ->assertSee('no corresponde a la clave privada')
            ->assertDontSee('Configuración lista para facturar.');
    });
});

test('uploading a matching certificate updates only the local credential file', function () {
    $this->actingAs(User::factory()->admin()->create());

    ($this->withArcaFiles)(function () {
        File::delete(afipDir().'cert');
        $this->post(route('config.newCert'), [
            'cert' => UploadedFile::fake()->createWithContent('certificate.crt', $this->arcaCertificate),
            'vencimiento' => now()->addDays(30)->toDateString(),
        ])->assertRedirect()->assertSessionHasNoErrors();

        expect(File::get(afipDir().'cert'))->toBe($this->arcaCertificate);
        expect(variable_global('AFIP_CERTIFICADO'))->toBe('obsolete database certificate');
    });
});

test('uploading a mismatched certificate preserves the installed certificate', function () {
    $this->actingAs(User::factory()->admin()->create());

    ($this->withArcaFiles)(function () {
        $differentKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($differentKey, $privateKey);
        File::put(afipDir().'key', $privateKey);
        File::put(afipDir().'cert', 'previous certificate');

        $this->post(route('config.newCert'), [
            'cert' => UploadedFile::fake()->createWithContent('certificate.crt', $this->arcaCertificate),
            'vencimiento' => now()->addDays(30)->toDateString(),
        ])->assertRedirect();

        expect(File::get(afipDir().'cert'))->toBe('previous certificate');
        expect(session('alert.config'))->toContain('no corresponde a la clave privada');
    });
});

test('generating a private key stores it only in the local credential file', function () {
    $this->actingAs(User::factory()->admin()->create());

    ($this->withArcaFiles)(function () {
        File::delete(afipDir().'key');

        $this->post(route('afip.makeKey'))->assertRedirect();

        expect(openssl_pkey_get_private(File::get(afipDir().'key')))->toBeInstanceOf(OpenSSLAsymmetricKey::class);
        expect(variable_global('AFIP_KEY'))->toBe('obsolete database key');
    });
});
