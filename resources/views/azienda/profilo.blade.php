@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Il Mio Profilo</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Modifica Profilo</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{ $utente->id }}">

                            <div class="row">
                                <!-- Immagine Profilo -->
                                <div class="col-lg-12 mb-4">
                                    <div class="text-center">
                                        <div class="position-relative d-inline-block">
                                            <div class="avatar-lg">
                                                <img src="{{ $utente->immagine ? asset($utente->immagine) : asset('default-user.png') }}"
                                                     alt="Immagine Profilo"
                                                     class="avatar-lg rounded-circle object-cover"
                                                     id="preview-img">
                                            </div>
                                            <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                                <input id="profile-img-file-input" type="file" name="immagine-user" class="profile-img-file-input" accept="image/*">
                                                <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                                    <span class="avatar-title rounded-circle bg-light text-body">
                                                        <i class="ri-camera-fill"></i>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dati Personali -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nome <span class="text-danger">*</span></label>
                                        <input type="text" name="nome" class="form-control" value="{{ $utente->nome }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Cognome <span class="text-danger">*</span></label>
                                        <input type="text" name="cognome" class="form-control" value="{{ $utente->cognome }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Data di Nascita</label>
                                        <input type="date" name="data_nascita" class="form-control" value="{{ $utente->data_nascita }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Luogo di Nascita</label>
                                        <input type="text" name="luogo_nascita" class="form-control" value="{{ $utente->luogo_nascita }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" value="{{ $utente->email }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Telefono</label>
                                        <input type="tel" name="telefono" class="form-control" value="{{ $utente->telefono }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <div class="position-relative">
                                            <input type="password" name="password" class="form-control" value="{{ $utente->password }}" id="password-input">
                                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted"
                                                    type="button" id="password-addon">
                                                <i class="ri-eye-fill align-middle"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Lascia vuoto per mantenere la password attuale</div>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="hstack gap-2 justify-content-end">
                                        <button type="button" class="btn btn-light" onclick="history.back()">Annulla</button>
                                        <button type="submit" name="modifica" class="btn btn-primary">Salva Modifiche</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Preview immagine
        document.getElementById('profile-img-file-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Toggle password visibility
        document.getElementById('password-addon').addEventListener('click', function() {
            const passwordInput = document.getElementById('password-input');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('ri-eye-fill');
                icon.classList.add('ri-eye-off-fill');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('ri-eye-off-fill');
                icon.classList.add('ri-eye-fill');
            }
        });
    });
</script>

<style>
    .profile-photo-edit {
        position: absolute;
        bottom: 0;
        right: 0;
    }

    .profile-img-file-input {
        display: none;
    }

    .avatar-lg {
        width: 120px;
        height: 120px;
    }

    .object-cover {
        object-fit: cover;
        width: 100%;
        height: 100%;
    }
</style>

@include('azienda.common.footer')