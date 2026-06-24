<div>
    {{-- ── Trigger Button ──────────────────────────────────────────────────── --}}
    <button wire:click="openModal" type="button"
        class="btn btn-success btn-sm rounded-pill px-3"
        title="Add walk-in attendee">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Walk-in
    </button>

    {{-- ── Modal ───────────────────────────────────────────────────────────── --}}
    @if($showModal)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);" wire:key="walkin-modal">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2 text-success">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Walk-in Registration
                        </h5>
                        <p class="text-muted small mb-0">
                            {{ $event->title }} — Registrant will be immediately approved & checked in.
                        </p>
                    </div>
                    <button wire:click="closeModal" type="button" class="btn-close" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if($showSuccess && $lastReg)
                        {{-- ── Success card ────────────────────────────────── --}}
                        <div class="text-center py-4">
                            <div class="mb-3 text-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h5 class="fw-bold mb-1">Walk-in Registered & Checked In!</h5>
                            <p class="text-muted mb-4">
                                {{ $lastReg->full_name ?? $lastReg->name }} has been successfully registered
                                and checked in as a walk-in attendee.
                            </p>

                            {{-- Mini QR --}}
                            <div class="mb-4">
                                <img src="{{ route('events.qr', [$event->slug, $lastReg->uuid]) }}"
                                    alt="QR Code"
                                    class="img-fluid rounded-3 border shadow-sm"
                                    style="width: 160px; height: 160px; image-rendering: pixelated;">
                                <p class="text-muted mt-2" style="font-size: 0.75rem;">
                                    UUID: <code>{{ $lastReg->uuid }}</code>
                                </p>
                            </div>

                            <div class="d-flex gap-2 justify-content-center">
                                <button wire:click="openModal" type="button"
                                    class="btn btn-success rounded-pill px-4">
                                    Register Another
                                </button>
                                <button wire:click="closeModal" type="button"
                                    class="btn btn-outline-secondary rounded-pill px-4">
                                    Close
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- ── Form ──────────────────────────────────────── --}}

                        @if($errors->has('capacity'))
                            <div class="alert alert-danger rounded-3 mb-4">{{ $errors->first('capacity') }}</div>
                        @endif

                        <form wire:submit.prevent="register" novalidate>
                            <div class="row g-3">

                                {{-- Salutation + Full Name --}}
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold">Salutation</label>
                                    <select wire:model="salutation"
                                        class="form-select form-select-sm @error('salutation') is-invalid @enderror">
                                        <option value="">—</option>
                                        <option value="Mr">Mr</option>
                                        <option value="Ms">Ms</option>
                                        <option value="Mrs">Mrs</option>
                                    </select>
                                    @error('salutation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-10">
                                    <label class="form-label small fw-semibold">
                                        Full Name <span class="text-danger">*</span>
                                    </label>
                                    <input wire:model.blur="full_name" type="text"
                                        class="form-control form-control-sm @error('full_name') is-invalid @enderror"
                                        placeholder="Full name">
                                    @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Company + Job Title --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">
                                        Company <span class="text-danger">*</span>
                                    </label>
                                    <input wire:model.blur="company_name" type="text"
                                        class="form-control form-control-sm @error('company_name') is-invalid @enderror"
                                        placeholder="PT Example">
                                    @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">
                                        Job Title <span class="text-danger">*</span>
                                    </label>
                                    <input wire:model.blur="job_title" type="text"
                                        class="form-control form-control-sm @error('job_title') is-invalid @enderror"
                                        placeholder="Cloud Engineer">
                                    @error('job_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Level + Division --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">
                                        Job Level <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="contact_level_id"
                                        class="form-select form-select-sm @error('contact_level_id') is-invalid @enderror">
                                        <option value="0" disabled>Select...</option>
                                        @foreach($contactLevels as $lvl)
                                            <option value="{{ $lvl->id }}">{{ $lvl->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('contact_level_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">
                                        Division <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model.live="contact_divisi_id"
                                        class="form-select form-select-sm @error('contact_divisi_id') is-invalid @enderror">
                                        <option value="0" disabled>Select...</option>
                                        @foreach($contactDivisions as $div)
                                            <option value="{{ $div->id }}">{{ $div->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('contact_divisi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                @if($contact_divisi_id == 5)
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">
                                        Specify Division <span class="text-danger">*</span>
                                    </label>
                                    <input wire:model.blur="contact_divisi_name" type="text"
                                        class="form-control form-control-sm @error('contact_divisi_name') is-invalid @enderror"
                                        placeholder="e.g., R&D">
                                    @error('contact_divisi_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                @endif

                                {{-- Phone --}}
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Country</label>
                                    <select wire:model="country_code"
                                        class="form-select form-select-sm">
                                        @foreach($countries as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label small fw-semibold">
                                        Phone <span class="text-danger">*</span>
                                    </label>
                                    <input wire:model.blur="mobile_phone" type="tel"
                                        class="form-control form-control-sm @error('mobile_phone') is-invalid @enderror"
                                        placeholder="81234567890">
                                    @error('mobile_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Email --}}
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <input wire:model.blur="email" type="email"
                                        class="form-control form-control-sm @error('email') is-invalid @enderror"
                                        placeholder="attendee@company.com">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Notes --}}
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-muted">
                                        Notes <span class="fw-normal">(optional)</span>
                                    </label>
                                    <input wire:model="notes" type="text"
                                        class="form-control form-control-sm"
                                        placeholder="Any special notes?">
                                </div>

                                {{-- Walk-in info banner --}}
                                <div class="col-12">
                                    <div class="alert alert-success alert-sm py-2 px-3 rounded-3 mb-0"
                                        style="font-size: 0.82rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Walk-in registrations are <strong>immediately approved & checked in</strong>.
                                        No approval required.
                                    </div>
                                </div>

                            </div>{{-- /row --}}

                            <div class="modal-footer border-0 px-0 pb-0 mt-3">
                                <button wire:click="closeModal" type="button"
                                    class="btn btn-outline-secondary rounded-pill px-4">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="btn btn-success rounded-pill px-4"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="register">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Register Walk-in
                                    </span>
                                    <span wire:loading wire:target="register">
                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                        Registering...
                                    </span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>{{-- /modal-body --}}
            </div>
        </div>
    </div>
    @endif
</div>
