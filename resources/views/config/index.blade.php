<x-app-layout>
    @php
        $variablesByKey = $variables->keyBy('clave');

        $getValue = function (string $key) use ($variablesByKey) {
            return old('config.'.$key, $variablesByKey->get($key)?->valor ?? '');
        };

        $requiredBillingFields = [
            'CUIT_EMPRESA' => [
                'label' => 'CUIT/CUIL',
                'description' => 'Obligatorio para la firma digital de facturas.',
                'type' => 'text',
            ],
            'RAZON_SOCIAL' => [
                'label' => 'Razón Social',
                'description' => 'Tal cual figura en ARCA/AFIP.',
                'type' => 'text',
            ],
            'DOMICILIO_FISCAL' => [
                'label' => 'Domicilio Fiscal',
                'description' => 'Usado en comprobantes y validaciones fiscales.',
                'type' => 'text',
            ],
            'FECHA_INICIO_ACTIVIDADES' => [
                'label' => 'Fecha de inicio de actividades',
                'description' => 'Si no se dispone del dato, solicitarlo al contador/a.',
                'type' => 'date',
            ],
            'CONDICION_IVA' => [
                'label' => 'Condición frente al IVA',
                'description' => 'Se utiliza para determinar reglas de facturación.',
                'type' => 'select',
            ],
            'PUNTO_VENTA' => [
                'label' => 'Punto de Venta',
                'description' => 'Sin este valor no se puede facturar.',
                'type' => 'text',
            ],
        ];

        $optionalFields = [
            'TIPO_IMPRESORA' => [
                'label' => 'Tipo de Impresora',
                'description' => 'Permite definir la impresión por defecto para comprobantes.',
                'type' => 'select',
            ],
            'TOPE_FACTURACION_CONSUMIDOR_FINAL' => [
                'label' => 'Tope facturación consumidor final',
                'description' => 'Valor de referencia para controles internos.',
                'type' => 'text',
            ],
        ];

        $condicionIvaOptions = [
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

        $impresoraOptions = ['AMBAS', 'TERMICA', 'A4'];

        $isMissing = function (string $key) use ($getValue): bool {
            return trim((string) $getValue($key)) === '';
        };

        $missingRequiredFields = collect($requiredBillingFields)->filter(fn ($field, $key) => $isMissing($key));

        $afipKeyMissing = trim((string) variable_global('AFIP_KEY')) === '';
        $afipCertificateMissing = trim((string) variable_global('AFIP_CERTIFICADO')) === '';
        $afipCertificateExpired = variable_global('VENCIMIENTO_CERTIFICADO')
            && Carbon\Carbon::parse(variable_global('VENCIMIENTO_CERTIFICADO'))->isPast();

        $missingSystemItems = collect([
            ['label' => 'Clave privada AFIP', 'missing' => $afipKeyMissing],
            ['label' => 'Certificado AFIP', 'missing' => $afipCertificateMissing],
        ])->filter(fn ($item) => $item['missing']);

        $hasBlockingWarnings = $missingRequiredFields->isNotEmpty() || $missingSystemItems->isNotEmpty();
    @endphp

    <div class="container pb-5">
        <x-title title="Configuraciones" urlBack="{{ route('home') }}" />

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert {{ $hasBlockingWarnings ? 'alert-warning' : 'alert-success' }} d-flex align-items-start gap-2 mb-4">
            <i class="fas {{ $hasBlockingWarnings ? 'fa-exclamation-triangle' : 'fa-check-circle' }} mt-1"></i>
            <div>
                @if ($hasBlockingWarnings)
                    <div class="fw-semibold">
                        Faltan datos para facturar correctamente.
                    </div>
                    <div class="small">
                        Completá estos campos para habilitar una facturación sin bloqueos:
                    </div>
                    <ul class="mb-0 mt-1">
                        @foreach ($missingRequiredFields as $field)
                            <li>{{ $field['label'] }}</li>
                        @endforeach
                        @foreach ($missingSystemItems as $item)
                            <li>{{ $item['label'] }}</li>
                        @endforeach
                    </ul>
                @else
                    <div class="fw-semibold">Configuración lista para facturar.</div>
                    <div class="small">No se detectaron campos críticos pendientes.</div>
                @endif
            </div>
        </div>

        <form id="configBulkForm" method="POST" action="{{ route('config.bulkUpdate') }}" class="d-flex flex-column gap-3">
            @csrf
            @method('PATCH')

            @foreach ($requiredBillingFields as $key => $field)
                @php
                    $fieldValue = (string) $getValue($key);
                    $missing = $isMissing($key);
                @endphp
                <div class="card shadow-sm" data-config-card="{{ $key }}" data-config-label="{{ $field['label'] }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div>
                                <h5 class="card-title mb-1">{{ $field['label'] }}</h5>
                                <p class="text-muted mb-0">{{ $field['description'] }}</p>
                            </div>
                            <span class="badge text-bg-warning config-warning-badge {{ $missing ? '' : 'd-none' }}">
                                <i class="fas fa-exclamation-triangle me-1"></i> Obligatorio para facturar
                            </span>
                        </div>

                        @if ($field['type'] === 'date')
                            <input
                                class="form-control @error('config.'.$key) is-invalid @enderror"
                                type="date"
                                name="config[{{ $key }}]"
                                value="{{ $fieldValue }}"
                            >
                        @elseif ($field['type'] === 'select')
                            <select class="form-select @error('config.'.$key) is-invalid @enderror" name="config[{{ $key }}]">
                                <option value="">Seleccione una opción</option>
                                @foreach ($condicionIvaOptions as $option)
                                    <option value="{{ $option }}" {{ $fieldValue === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        @else
                            <input
                                class="form-control @error('config.'.$key) is-invalid @enderror"
                                type="text"
                                name="config[{{ $key }}]"
                                value="{{ $fieldValue }}"
                            >
                        @endif

                        @error('config.'.$key)
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <small class="text-warning config-warning-text {{ $missing ? '' : 'd-none' }}">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Si no completás este campo no vas a poder facturar correctamente.
                        </small>
                    </div>
                </div>
            @endforeach

            @foreach ($optionalFields as $key => $field)
                @php
                    $fieldValue = (string) $getValue($key);
                @endphp
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-1">{{ $field['label'] }}</h5>
                        <p class="text-muted mb-3">{{ $field['description'] }}</p>

                        @if ($field['type'] === 'select')
                            <select class="form-select @error('config.'.$key) is-invalid @enderror" name="config[{{ $key }}]">
                                <option value="">Seleccione una opción</option>
                                @foreach ($impresoraOptions as $option)
                                    <option value="{{ $option }}" {{ $fieldValue === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        @else
                            <input
                                class="form-control @error('config.'.$key) is-invalid @enderror"
                                type="text"
                                name="config[{{ $key }}]"
                                value="{{ $fieldValue }}"
                            >
                        @endif

                        @error('config.'.$key)
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endforeach
        </form>

        <hr class="my-4">

        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Imagen para membrete</h5>
                        <p class="text-muted">Se utiliza en documentos exportados.</p>

                        @if ($avatarPath = avatar_storage_relative_path())
                            @if (Storage::disk('public')->exists($avatarPath))
                                <img class="img-fluid img-thumbnail mb-3 config-avatar-preview" src="{{ avatar_public_url() }}">
                            @else
                                <div class="alert alert-warning">La imagen configurada no existe en el disco público.</div>
                            @endif
                        @else
                            <div class="alert alert-warning">No hay una imagen cargada.</div>
                        @endif

                        <form method="POST" action="{{ route('config.avatar') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" class="form-control mb-3" name="avatar" accept="image/*" required />
                            <button class="btn btn-success">Guardar imagen</button>
                            @if (variable_global('AVATAR'))
                                <button onclick="eliminarAvatar()" class="btn btn-outline-danger" type="button" title="Remover imagen">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            Clave privada AFIP
                            @if ($afipKeyMissing)
                                <i class="fas fa-exclamation-triangle text-warning ms-1" title="Falta la clave privada"></i>
                            @endif
                        </h5>
                        <p class="text-muted">Se utiliza para firmar certificados y comprobantes.</p>

                        @if (! variable_global('AFIP_KEY'))
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Todavía no existe una clave privada.
                            </div>
                            <form method="POST" action="{{ route('afip.makeKey') }}">
                                @csrf
                                <button class="btn btn-warning">Generar clave <i class="fas fa-key"></i></button>
                            </form>
                        @else
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-1"></i>
                                La clave privada ya está generada.
                            </div>
                        @endif

                        <a href="{{ route('afip.makeCSR') }}" class="btn btn-outline-primary">
                            Descargar solicitud CSR
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            Certificado AFIP
                            @if ($afipCertificateMissing || $afipCertificateExpired)
                                <i class="fas fa-exclamation-triangle text-warning ms-1" title="Certificado pendiente o vencido"></i>
                            @endif
                        </h5>
                        <p class="text-muted">Certificado emitido por ARCA/AFIP para la facturación electrónica.</p>

                        @if (variable_global('AFIP_CERTIFICADO'))
                            @if ($afipCertificateExpired)
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <strong>Certificado vencido.</strong>
                                    Venció {{ Carbon\Carbon::parse(variable_global('VENCIMIENTO_CERTIFICADO'))->diffForHumans() }}.
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Certificado cargado.
                                    Vence el <strong>{{ date('d/m/Y', strtotime(variable_global('VENCIMIENTO_CERTIFICADO'))) }}</strong>
                                    <small>({{ Carbon\Carbon::parse(variable_global('VENCIMIENTO_CERTIFICADO'))->diffForHumans() }})</small>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                No hay certificado cargado.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('config.newCert') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Archivo del certificado</label>
                                <input type="file" class="form-control" required name="cert" value="{{ old('cert') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Vencimiento</label>
                                <input type="date" class="form-control" name="vencimiento" value="{{ variable_global('VENCIMIENTO_CERTIFICADO') }}" required />
                            </div>
                            <button class="btn btn-success w-100">Guardar certificado</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" id="formEliminarMembrete" action="{{ route('avatar.destroy') }}">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <div id="configStickySaveBar" class="config-sticky-save-bar d-none">
        <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2 py-2">
            <div>
                <div class="fw-semibold">Cambios sin guardar</div>
                <small class="text-muted">Guardá una sola vez para aplicar todas las modificaciones.</small>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" id="discardConfigChanges">Descartar</button>
                <button type="submit" class="btn btn-success" form="configBulkForm" id="saveConfigChangesButton">
                    <i class="fas fa-save me-1"></i> Guardar cambios
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const configForm = document.getElementById('configBulkForm');
                const stickyBar = document.getElementById('configStickySaveBar');
                const discardButton = document.getElementById('discardConfigChanges');
                const saveButton = document.getElementById('saveConfigChangesButton');
                const requiredKeys = @json(array_keys($requiredBillingFields));

                if (!configForm || !stickyBar) {
                    return;
                }

                const serializeForm = () => new URLSearchParams(new FormData(configForm)).toString();
                const initialFormState = serializeForm();

                const refreshStickyBar = () => {
                    stickyBar.classList.toggle('d-none', serializeForm() === initialFormState);
                };

                const refreshRequiredWarnings = () => {
                    requiredKeys.forEach((key) => {
                        const input = configForm.querySelector(`[name="config[${key}]"]`);
                        const card = document.querySelector(`[data-config-card="${key}"]`);

                        if (!input || !card) {
                            return;
                        }

                        const missing = input.value.trim() === '';
                        card.querySelector('.config-warning-badge')?.classList.toggle('d-none', !missing);
                        card.querySelector('.config-warning-text')?.classList.toggle('d-none', !missing);
                    });
                };

                configForm.addEventListener('input', () => {
                    refreshStickyBar();
                    refreshRequiredWarnings();
                });

                configForm.addEventListener('change', () => {
                    refreshStickyBar();
                    refreshRequiredWarnings();
                });

                configForm.addEventListener('submit', () => {
                    saveButton.disabled = true;
                    saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';
                });

                discardButton?.addEventListener('click', () => {
                    configForm.reset();
                    refreshRequiredWarnings();
                    refreshStickyBar();
                });

                refreshRequiredWarnings();
                refreshStickyBar();
            });

            const eliminarAvatar = () => {
                Swal.fire({
                    icon: 'question',
                    title: '¿Eliminar la imagen del membrete?',
                    showCancelButton: true,
                    confirmButtonText: 'Si',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('formEliminarMembrete').submit();
                    }
                });
            }
        </script>
    @endpush

    <style>
        .config-sticky-save-bar {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1035;
            border-top: 1px solid var(--bs-border-color);
            background-color: var(--bs-body-bg);
            box-shadow: 0 -0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .config-avatar-preview {
            width: auto;
            max-width: 100%;
            max-height: 180px;
            display: block;
            margin-inline: auto;
            object-fit: contain;
        }
    </style>
</x-app-layout>
