@extends('iccom::layouts.app')

@section('title', 'Become a Member - iCCom Indonesia Cloud Community')

@section('content')
    <div style="height: 100px;"></div> <!-- Spacer logic from template .spacer css might need to be checked, using inline style for safety -->

    <style>
        .form-control-flushed.is-invalid,
        .form-select.is-invalid {
            border-bottom-color: #EF4444 !important;
        }
    </style>

    <!-- Form Section (Overlapping) -->
    <section class="membership-form-section pb-5 position-relative">
        <div class="container">
            <div class="text-white text-center" data-aos="fade-down">
                <h1 class="display-4 fw-bold mb-3">Join iCCom Membership</h1>
                <p class="lead mb-2 text-white-50">Sign up for free and take part in social and educational activities
                    designed with you in mind.</p>
                <p class="lead mb-2 text-white-50">Discover new insights at our events, learn from member-written
                    articles,
                    and connect with people who share the same passion for cloud.</p>
                <h2 class="display-5 fw-bold mt-4">Let's #UnitedatCloud</h2>
            </div>

            <div class="publish-form-card bg-white rounded-4 p-5 shadow-lg mx-auto mt-5" data-aos="fade-up" data-aos-delay="100">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    action="{{ route('membership.store') }}"
                    method="POST"
                    x-data="{
                        errors: {},
                        validate() {
                            this.errors = {};
                            let isValid = true;
                            
                            // Name validation
                            let nameVal = this.$el.querySelector('[name=name]')?.value || '';
                            if (!nameVal.trim()) {
                                this.errors.name = 'Name is required.';
                                isValid = false;
                            }
                            
                            // Email validation
                            let emailVal = this.$el.querySelector('[name=email]')?.value || '';
                            if (!emailVal.trim()) {
                                this.errors.email = 'E-mail is required.';
                                isValid = false;
                            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
                                this.errors.email = 'Please enter a valid e-mail address.';
                                isValid = false;
                            }
                            
                            return isValid;
                        },
                        clearError(name) {
                            if (this.errors[name]) {
                                delete this.errors[name];
                            }
                        }
                    }"
                    @submit="if (!validate()) { $event.preventDefault(); $event.stopPropagation(); return false; }"
                    @input="clearError($event.target.name)"
                    @change="clearError($event.target.name)"
                    novalidate
                >
                    @csrf
                    <div class="row g-4">
                        <!-- Row 1 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Name: <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                class="form-control form-control-flushed @error('name') is-invalid @enderror"
                                :class="errors.name ? 'is-invalid' : ''"
                                placeholder="Name" value="{{ old('name') }}" required>
                            <template x-if="errors.name">
                                <div class="text-danger small mt-1" style="font-size:0.75rem;" x-text="errors.name"></div>
                            </template>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Job Level:</label>
                            <select name="job_level" class="form-select form-control-flushed">
                                <option value="">Select Job Level</option>
                                <option value="Entry Level" {{ old('job_level') == 'Entry Level' ? 'selected' : '' }}>Entry Level</option>
                                <option value="Mid Level" {{ old('job_level') == 'Mid Level' ? 'selected' : '' }}>Mid Level</option>
                                <option value="Senior Level" {{ old('job_level') == 'Senior Level' ? 'selected' : '' }}>Senior Level</option>
                                <option value="Manager" {{ old('job_level') == 'Manager' ? 'selected' : '' }}>Manager</option>
                                <option value="Director" {{ old('job_level') == 'Director' ? 'selected' : '' }}>Director</option>
                                <option value="C-Level" {{ old('job_level') == 'C-Level' ? 'selected' : '' }}>C-Level</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Domicile:</label>
                            <select name="domicile" class="form-select form-control-flushed">
                                <option value="">Select Domicile</option>

                                {{-- Aceh --}}
                                <option value="Banda Aceh" {{ old('domicile') == 'Banda Aceh' ? 'selected' : '' }}>Banda Aceh</option>
                                <option value="Langsa" {{ old('domicile') == 'Langsa' ? 'selected' : '' }}>Langsa</option>
                                <option value="Lhokseumawe" {{ old('domicile') == 'Lhokseumawe' ? 'selected' : '' }}>Lhokseumawe</option>
                                <option value="Sabang" {{ old('domicile') == 'Sabang' ? 'selected' : '' }}>Sabang</option>
                                <option value="Subulussalam" {{ old('domicile') == 'Subulussalam' ? 'selected' : '' }}>Subulussalam</option>

                                {{-- Sumatera Utara --}}
                                <option value="Medan" {{ old('domicile') == 'Medan' ? 'selected' : '' }}>Medan</option>
                                <option value="Binjai" {{ old('domicile') == 'Binjai' ? 'selected' : '' }}>Binjai</option>
                                <option value="Padangsidimpuan" {{ old('domicile') == 'Padangsidimpuan' ? 'selected' : '' }}>Padangsidimpuan</option>
                                <option value="Pematangsiantar" {{ old('domicile') == 'Pematangsiantar' ? 'selected' : '' }}>Pematangsiantar</option>
                                <option value="Sibolga" {{ old('domicile') == 'Sibolga' ? 'selected' : '' }}>Sibolga</option>
                                <option value="Tanjungbalai" {{ old('domicile') == 'Tanjungbalai' ? 'selected' : '' }}>Tanjungbalai</option>
                                <option value="Tebing Tinggi" {{ old('domicile') == 'Tebing Tinggi' ? 'selected' : '' }}>Tebing Tinggi</option>

                                {{-- Sumatera Barat --}}
                                <option value="Padang" {{ old('domicile') == 'Padang' ? 'selected' : '' }}>Padang</option>
                                <option value="Bukittinggi" {{ old('domicile') == 'Bukittinggi' ? 'selected' : '' }}>Bukittinggi</option>
                                <option value="Padang Panjang" {{ old('domicile') == 'Padang Panjang' ? 'selected' : '' }}>Padang Panjang</option>
                                <option value="Pariaman" {{ old('domicile') == 'Pariaman' ? 'selected' : '' }}>Pariaman</option>
                                <option value="Payakumbuh" {{ old('domicile') == 'Payakumbuh' ? 'selected' : '' }}>Payakumbuh</option>
                                <option value="Sawahlunto" {{ old('domicile') == 'Sawahlunto' ? 'selected' : '' }}>Sawahlunto</option>
                                <option value="Solok" {{ old('domicile') == 'Solok' ? 'selected' : '' }}>Solok</option>

                                {{-- Riau --}}
                                <option value="Pekanbaru" {{ old('domicile') == 'Pekanbaru' ? 'selected' : '' }}>Pekanbaru</option>
                                <option value="Dumai" {{ old('domicile') == 'Dumai' ? 'selected' : '' }}>Dumai</option>

                                {{-- Kepulauan Riau --}}
                                <option value="Batam" {{ old('domicile') == 'Batam' ? 'selected' : '' }}>Batam</option>
                                <option value="Tanjung Pinang" {{ old('domicile') == 'Tanjung Pinang' ? 'selected' : '' }}>Tanjung Pinang</option>

                                {{-- Jambi --}}
                                <option value="Jambi" {{ old('domicile') == 'Jambi' ? 'selected' : '' }}>Jambi</option>
                                <option value="Sungai Penuh" {{ old('domicile') == 'Sungai Penuh' ? 'selected' : '' }}>Sungai Penuh</option>

                                {{-- Sumatera Selatan --}}
                                <option value="Palembang" {{ old('domicile') == 'Palembang' ? 'selected' : '' }}>Palembang</option>
                                <option value="Lubuklinggau" {{ old('domicile') == 'Lubuklinggau' ? 'selected' : '' }}>Lubuklinggau</option>
                                <option value="Pagar Alam" {{ old('domicile') == 'Pagar Alam' ? 'selected' : '' }}>Pagar Alam</option>
                                <option value="Prabumulih" {{ old('domicile') == 'Prabumulih' ? 'selected' : '' }}>Prabumulih</option>

                                {{-- Bengkulu --}}
                                <option value="Bengkulu" {{ old('domicile') == 'Bengkulu' ? 'selected' : '' }}>Bengkulu</option>

                                {{-- Lampung --}}
                                <option value="Bandar Lampung" {{ old('domicile') == 'Bandar Lampung' ? 'selected' : '' }}>Bandar Lampung</option>
                                <option value="Metro" {{ old('domicile') == 'Metro' ? 'selected' : '' }}>Metro</option>

                                {{-- DKI Jakarta --}}
                                <option value="Jakarta Pusat" {{ old('domicile') == 'Jakarta Pusat' ? 'selected' : '' }}>Jakarta Pusat</option>
                                <option value="Jakarta Utara" {{ old('domicile') == 'Jakarta Utara' ? 'selected' : '' }}>Jakarta Utara</option>
                                <option value="Jakarta Barat" {{ old('domicile') == 'Jakarta Barat' ? 'selected' : '' }}>Jakarta Barat</option>
                                <option value="Jakarta Selatan" {{ old('domicile') == 'Jakarta Selatan' ? 'selected' : '' }}>Jakarta Selatan</option>
                                <option value="Jakarta Timur" {{ old('domicile') == 'Jakarta Timur' ? 'selected' : '' }}>Jakarta Timur</option>

                                {{-- Jawa Barat --}}
                                <option value="Bandung" {{ old('domicile') == 'Bandung' ? 'selected' : '' }}>Bandung</option>
                                <option value="Bekasi" {{ old('domicile') == 'Bekasi' ? 'selected' : '' }}>Bekasi</option>
                                <option value="Bogor" {{ old('domicile') == 'Bogor' ? 'selected' : '' }}>Bogor</option>
                                <option value="Cimahi" {{ old('domicile') == 'Cimahi' ? 'selected' : '' }}>Cimahi</option>
                                <option value="Cirebon" {{ old('domicile') == 'Cirebon' ? 'selected' : '' }}>Cirebon</option>
                                <option value="Depok" {{ old('domicile') == 'Depok' ? 'selected' : '' }}>Depok</option>
                                <option value="Sukabumi" {{ old('domicile') == 'Sukabumi' ? 'selected' : '' }}>Sukabumi</option>
                                <option value="Tasikmalaya" {{ old('domicile') == 'Tasikmalaya' ? 'selected' : '' }}>Tasikmalaya</option>

                                {{-- Jawa Tengah --}}
                                <option value="Semarang" {{ old('domicile') == 'Semarang' ? 'selected' : '' }}>Semarang</option>
                                <option value="Surakarta" {{ old('domicile') == 'Surakarta' ? 'selected' : '' }}>Surakarta</option>
                                <option value="Magelang" {{ old('domicile') == 'Magelang' ? 'selected' : '' }}>Magelang</option>
                                <option value="Pekalongan" {{ old('domicile') == 'Pekalongan' ? 'selected' : '' }}>Pekalongan</option>
                                <option value="Salatiga" {{ old('domicile') == 'Salatiga' ? 'selected' : '' }}>Salatiga</option>
                                <option value="Tegal" {{ old('domicile') == 'Tegal' ? 'selected' : '' }}>Tegal</option>

                                {{-- DI Yogyakarta --}}
                                <option value="Yogyakarta" {{ old('domicile') == 'Yogyakarta' ? 'selected' : '' }}>Yogyakarta</option>

                                {{-- Jawa Timur --}}
                                <option value="Surabaya" {{ old('domicile') == 'Surabaya' ? 'selected' : '' }}>Surabaya</option>
                                <option value="Malang" {{ old('domicile') == 'Malang' ? 'selected' : '' }}>Malang</option>
                                <option value="Batu" {{ old('domicile') == 'Batu' ? 'selected' : '' }}>Batu</option>
                                <option value="Blitar" {{ old('domicile') == 'Blitar' ? 'selected' : '' }}>Blitar</option>
                                <option value="Kediri" {{ old('domicile') == 'Kediri' ? 'selected' : '' }}>Kediri</option>
                                <option value="Madiun" {{ old('domicile') == 'Madiun' ? 'selected' : '' }}>Madiun</option>
                                <option value="Mojokerto" {{ old('domicile') == 'Mojokerto' ? 'selected' : '' }}>Mojokerto</option>
                                <option value="Pasuruan" {{ old('domicile') == 'Pasuruan' ? 'selected' : '' }}>Pasuruan</option>
                                <option value="Probolinggo" {{ old('domicile') == 'Probolinggo' ? 'selected' : '' }}>Probolinggo</option>

                                {{-- Bali --}}
                                <option value="Denpasar" {{ old('domicile') == 'Denpasar' ? 'selected' : '' }}>Denpasar</option>

                                {{-- Kalimantan --}}
                                <option value="Pontianak" {{ old('domicile') == 'Pontianak' ? 'selected' : '' }}>Pontianak</option>
                                <option value="Palangkaraya" {{ old('domicile') == 'Palangkaraya' ? 'selected' : '' }}>Palangkaraya</option>
                                <option value="Banjarmasin" {{ old('domicile') == 'Banjarmasin' ? 'selected' : '' }}>Banjarmasin</option>
                                <option value="Banjarbaru" {{ old('domicile') == 'Banjarbaru' ? 'selected' : '' }}>Banjarbaru</option>
                                <option value="Samarinda" {{ old('domicile') == 'Samarinda' ? 'selected' : '' }}>Samarinda</option>
                                <option value="Balikpapan" {{ old('domicile') == 'Balikpapan' ? 'selected' : '' }}>Balikpapan</option>
                                <option value="Tarakan" {{ old('domicile') == 'Tarakan' ? 'selected' : '' }}>Tarakan</option>
                                <option value="Nusantara" {{ old('domicile') == 'Nusantara' ? 'selected' : '' }}>Nusantara</option>

                                {{-- Sulawesi --}}
                                <option value="Makassar" {{ old('domicile') == 'Makassar' ? 'selected' : '' }}>Makassar</option>
                                <option value="Manado" {{ old('domicile') == 'Manado' ? 'selected' : '' }}>Manado</option>
                                <option value="Palu" {{ old('domicile') == 'Palu' ? 'selected' : '' }}>Palu</option>
                                <option value="Kendari" {{ old('domicile') == 'Kendari' ? 'selected' : '' }}>Kendari</option>
                                <option value="Gorontalo" {{ old('domicile') == 'Gorontalo' ? 'selected' : '' }}>Gorontalo</option>
                                <option value="Parepare" {{ old('domicile') == 'Parepare' ? 'selected' : '' }}>Parepare</option>
                                <option value="Palopo" {{ old('domicile') == 'Palopo' ? 'selected' : '' }}>Palopo</option>

                                {{-- Maluku --}}
                                <option value="Ambon" {{ old('domicile') == 'Ambon' ? 'selected' : '' }}>Ambon</option>
                                <option value="Tual" {{ old('domicile') == 'Tual' ? 'selected' : '' }}>Tual</option>

                                {{-- Papua --}}
                                <option value="Jayapura" {{ old('domicile') == 'Jayapura' ? 'selected' : '' }}>Jayapura</option>
                                <option value="Sorong" {{ old('domicile') == 'Sorong' ? 'selected' : '' }}>Sorong</option>

                                <option value="Other" {{ old('domicile') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <!-- Row 2 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">E-mail: <span class="text-danger">*</span></label>
                            <input type="email" name="email"
                                class="form-control form-control-flushed @error('email') is-invalid @enderror"
                                :class="errors.email ? 'is-invalid' : ''"
                                placeholder="Email" value="{{ old('email') }}" required>
                            <template x-if="errors.email">
                                <div class="text-danger small mt-1" style="font-size:0.75rem;" x-text="errors.email"></div>
                            </template>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Job Title:</label>
                            <input type="text" name="job_title" class="form-control form-control-flushed" placeholder="e.g. Software Engineer" value="{{ old('job_title') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Linkedin Account:</label>
                            <input type="text" name="linkedin" class="form-control form-control-flushed" placeholder="linkedin.com/in/username" value="{{ old('linkedin') }}">
                        </div>

                        <!-- Row 3 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Phone Number:</label>
                            <input type="text" name="phone" class="form-control form-control-flushed" placeholder="08xxxxxxxxxx" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Institution/Company:</label>
                            <input type="text" name="institution" class="form-control form-control-flushed" placeholder="Institution/Company" value="{{ old('institution') }}">
                        </div>
                        <div class="col-md-4">
                            <!-- Empty Column -->
                        </div>

                        <!-- Row 4 -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Highest Education Level:</label>
                            <select name="education_level" class="form-select form-control-flushed">
                                <option value="">Select Education Level</option>
                                <option value="SMA/SMK" {{ old('education_level') == 'SMA/SMK' ? 'selected' : '' }}>SMA/SMK</option>
                                <option value="D3" {{ old('education_level') == 'D3' ? 'selected' : '' }}>D3</option>
                                <option value="S1" {{ old('education_level') == 'S1' ? 'selected' : '' }}>S1</option>
                                <option value="S2" {{ old('education_level') == 'S2' ? 'selected' : '' }}>S2</option>
                                <option value="S3" {{ old('education_level') == 'S3' ? 'selected' : '' }}>S3</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Industry:</label>
                            <select name="industry" class="form-select form-control-flushed">
                                <option value="">Select Industry</option>
                                <option value="Technology" {{ old('industry') == 'Technology' ? 'selected' : '' }}>Technology</option>
                                <option value="Finance" {{ old('industry') == 'Finance' ? 'selected' : '' }}>Finance</option>
                                <option value="Healthcare" {{ old('industry') == 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
                                <option value="Education" {{ old('industry') == 'Education' ? 'selected' : '' }}>Education</option>
                                <option value="Government" {{ old('industry') == 'Government' ? 'selected' : '' }}>Government</option>
                                <option value="Retail" {{ old('industry') == 'Retail' ? 'selected' : '' }}>Retail</option>
                                <option value="Manufacturing" {{ old('industry') == 'Manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                                <option value="Consulting" {{ old('industry') == 'Consulting' ? 'selected' : '' }}>Consulting</option>
                                <option value="Other" {{ old('industry') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <!-- Empty Column -->
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12 text-center mt-5">
                            <button type="submit"
                                class="btn btn-cta btn-warning text-white fw-bold rounded-pill px-5 py-2 shadow">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('livewire-styles')
    @livewireStyles
@endpush

@push('livewire-scripts')
    @livewireScripts
@endpush
