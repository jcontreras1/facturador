<?php

namespace App\Http\Controllers;

use App\Models\VariableGlobal;
use App\Models\Arca\Iva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ConfigController extends Controller
{
    public function index(){
        $variables = VariableGlobal::all();
        $ivas = Iva::all();
        $afipKeyMissing = !is_file(afipDir().'key') || !is_readable(afipDir().'key');
        $afipCertificateMissing = !is_file(afipDir().'cert') || !is_readable(afipDir().'cert');
        $certificateContent = $afipCertificateMissing ? null : file_get_contents(afipDir().'cert');
        $certificateData = $certificateContent === null ? false : @openssl_x509_parse($certificateContent);
        $afipCertificateInvalid = !$afipCertificateMissing && $certificateData === false;
        $afipCertificateExpiration = $certificateData
            ? \Carbon\Carbon::createFromTimestamp($certificateData['validTo_time_t'])
            : null;
        $afipCredentialsMismatch = $certificateData && !$afipKeyMissing
            && !@openssl_x509_check_private_key($certificateContent, file_get_contents(afipDir().'key'));
        return view('config.index')->with(compact([
            'variables',
            'ivas',
            'afipKeyMissing',
            'afipCertificateMissing',
            'afipCertificateInvalid',
            'afipCertificateExpiration',
            'afipCredentialsMismatch',
        ]));
    }

    public function update(VariableGlobal $config, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'valor' => $config->clave === 'IVA_PREDETERMINADO'
                ? ['nullable', 'integer', 'exists:iva,id']
                : ['nullable', 'string'],
        ]);

        $config->update(['valor' => $validated['valor'] ?? null]);

        toast('Variable modificada', 'success')->autoClose(2000);
        return back();
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'config' => ['required', 'array'],
            'config.CUIT_EMPRESA' => ['nullable', 'string', 'max:30'],
            'config.RAZON_SOCIAL' => ['nullable', 'string', 'max:255'],
            'config.DOMICILIO_FISCAL' => ['nullable', 'string', 'max:255'],
            'config.FECHA_INICIO_ACTIVIDADES' => ['nullable', 'date'],
            'config.CONDICION_IVA' => ['nullable', 'string', Rule::in($this->condicionIvaOptions())],
            'config.IVA_PREDETERMINADO' => ['nullable', 'integer', 'exists:iva,id'],
            'config.PUNTO_VENTA' => ['nullable', 'string', 'max:20'],
            'config.TIPO_IMPRESORA' => ['nullable', 'string', Rule::in(['AMBAS', 'TERMICA', 'A4'])],
            'config.TOPE_FACTURACION_CONSUMIDOR_FINAL' => ['nullable', 'string', 'max:30'],
        ]);

        foreach ($this->bulkEditableConfigDefinitions() as $key => $definition) {
            if (! array_key_exists($key, $validated['config'])) {
                continue;
            }

            $value = $validated['config'][$key];
            if (is_string($value)) {
                $value = trim($value);
            }

            $variable = VariableGlobal::query()->firstOrNew(['clave' => $key]);
            $variable->clave = $key;
            if (! $variable->exists || blank($variable->descripcion)) {
                $variable->descripcion = $definition['descripcion'];
            }

            $variable->valor = $value === '' ? null : $value;
            $variable->save();
        }

        toast('Configuraciones actualizadas correctamente', 'success')->autoClose(2000);

        return back();
    }

    public function set_avatar(Request $request){
        $obj = obj_variable_global('AVATAR');
        if($request->hasFile('avatar')){

            $request->validate([
                'avatar' => 'mimes:jpg,jpeg,png,webp,tiff,svg|required|max:2000'
            ]);

            if ($oldAvatarPath = avatar_storage_relative_path($obj->valor)) {
                Storage::disk('public')->delete($oldAvatarPath);
            }

            $imageName = Str::uuid() . '.' . $request->avatar->extension();
            $path = $request->file('avatar')->storeAs('avatars', $imageName, 'public');
            $obj->update(['valor' => url('/storage/' . ltrim($path, '/'))]);
            toast('Imagen establecida', 'success')->autoClose(2000);
        }
        return back();
    }

    public function unset_avatar(Request $request){
        $obj = obj_variable_global('AVATAR');
        if($path = avatar_storage_relative_path($obj->valor)){
            Storage::disk('public')->delete($path);
        }
        $obj->update(['valor' => '']);
        toast('Imagen eliminada', 'success')->autoClose(2000);
        return back();
    }

    protected function bulkEditableConfigDefinitions(): array
    {
        return [
            'CUIT_EMPRESA' => ['descripcion' => 'Cuit de la empresa'],
            'RAZON_SOCIAL' => ['descripcion' => 'Razón social de la empresa'],
            'DOMICILIO_FISCAL' => ['descripcion' => 'Domicilio fiscal de la empresa'],
            'FECHA_INICIO_ACTIVIDADES' => ['descripcion' => 'Fecha de inicio de actividades de la empresa'],
            'CONDICION_IVA' => ['descripcion' => 'Condiciones de IVA para facturación electrónica'],
            'IVA_PREDETERMINADO' => ['descripcion' => 'Alícuota de IVA predeterminada para facturas A y B'],
            'PUNTO_VENTA' => ['descripcion' => 'Punto de venta para facturación electrónica'],
            'TIPO_IMPRESORA' => ['descripcion' => 'Estilo de impresora para la factura'],
            'TOPE_FACTURACION_CONSUMIDOR_FINAL' => ['descripcion' => 'Tope de facturación para responsable inscripto'],
        ];
    }

    protected function condicionIvaOptions(): array
    {
        return [
            'IVA Responsable Inscripto',
            'IVA Responsable No Inscripto',
            'IVA Exento',
            'IVA No Responsable',
            'IVA Sujeto Exento',
            'Consumidor Final',
            'Responsable Monotributo',
            'Sujeto No Categorizado',
            'Proveedor del Exterior',
            'Cliente del Exterior',
            'IVA Liberado – Ley Nº 19.640',
        ];
    }
}
