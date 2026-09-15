@php
    $statePath = $getStatePath();
    $imagePath = str_replace('image_upload', 'image', $statePath);
@endphp

<div
    x-data="{
        uploading: false,
        progress: 0,
        preview: '',
        dragging: false,

        async uploadImage(file) {

            if (!file) {
                return;
            }

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!allowedTypes.includes(file.type)) {
                alert('Format gambar harus JPG, PNG, atau WEBP.');
                return;
            }

            const maxSize = 25 * 1024 * 1024;

            if (file.size > maxSize) {
                alert('Ukuran gambar maksimal 25 MB.');
                return;
            }

            this.uploading = true;
            this.progress = 0;

            try {

                /* AUTH IMAGEKIT */

                const authResponse = await fetch(
                    '{{ route('imagekit.auth') }}',
                    {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                        },
                    }
                );

                if (!authResponse.ok) {
                    throw new Error(
                        'Gagal mendapatkan autentikasi ImageKit.'
                    );
                }

                const auth = await authResponse.json();

                /* PUBLIC KEY */

                const publicKey = @js(
                    config('services.imagekit.public_key')
                );

                if (!publicKey) {
                    throw new Error(
                        'ImageKit public key belum dikonfigurasi.'
                    );
                }

                /* FORM DATA */

                const formData = new FormData();

                formData.append(
                    'file',
                    file
                );

                formData.append(
                    'fileName',
                    `${Date.now()}-${file.name}`
                );

                formData.append(
                    'publicKey',
                    publicKey
                );

                formData.append(
                    'signature',
                    auth.signature
                );

                formData.append(
                    'expire',
                    auth.expire
                );

                formData.append(
                    'token',
                    auth.token
                );

                formData.append(
                    'folder',
                    '/portfolio'
                );

                formData.append(
                    'useUniqueFileName',
                    'true'
                );

                /* UPLOAD IMAGEKIT */

                const xhr = new XMLHttpRequest();

                const result = await new Promise(
                    (resolve, reject) => {

                        xhr.open(
                            'POST',
                            'https://upload.imagekit.io/api/v1/files/upload'
                        );

                        xhr.upload.addEventListener(
                            'progress',
                            (event) => {

                                if (event.lengthComputable) {

                                    this.progress = Math.round(
                                        (
                                            event.loaded /
                                            event.total
                                        ) * 100
                                    );

                                }

                            }
                        );

                        xhr.onload = () => {

                            if (
                                xhr.status >= 200 &&
                                xhr.status < 300
                            ) {

                                try {

                                    resolve(
                                        JSON.parse(
                                            xhr.responseText
                                        )
                                    );

                                } catch (error) {

                                    reject(
                                        new Error(
                                            'Response ImageKit tidak valid.'
                                        )
                                    );

                                }

                                return;
                            }

                            let message =
                                'Upload ImageKit gagal.';

                            try {

                                const errorResponse =
                                    JSON.parse(
                                        xhr.responseText
                                    );

                                if (
                                    errorResponse.message
                                ) {

                                    message =
                                        errorResponse.message;

                                }

                            } catch (error) {
                                // Abaikan parsing error.
                            }

                            reject(
                                new Error(message)
                            );
                        };

                        xhr.onerror = () => {

                            reject(
                                new Error(
                                    'Koneksi ke ImageKit gagal.'
                                )
                            );

                        };

                        xhr.send(formData);
                    }
                );

                /* CEK URL */

                if (!result.url) {

                    throw new Error(
                        'ImageKit tidak mengembalikan URL gambar.'
                    );

                }

                /* PREVIEW */

            this.preview = result.url;
            this.progress = 100;

            /*
            * Simpan URL ImageKit ke field image
            */
            await $wire.set(
                @js($imagePath),
                result.url
            );

            alert(
                'Gambar berhasil di-upload ke ImageKit.'
            );

            } catch (error) {

                console.error(
                    'ImageKit upload error:',
                    error
                );

                alert(
                    error.message ||
                    'Upload gambar gagal.'
                );

            } finally {

                this.uploading = false;

            }
        },

        /* FILE INPUT */

        handleFileInput(event) {

            const file =
                event.target.files[0];

            if (file) {
                this.uploadImage(file);
            }

            event.target.value = '';

        },

        /* DRAG & DROP */

        handleDrop(event) {

            this.dragging = false;

            if (this.uploading) {
                return;
            }

            const files =
                event.dataTransfer.files;

            if (
                files &&
                files.length > 0
            ) {

                this.uploadImage(
                    files[0]
                );

            }

        }
    }"
>
    <div
        @dragover.prevent="
            if (!uploading) {
                dragging = true;
            }
        "
        @dragleave.prevent="
            dragging = false;
        "
        @drop.prevent="
            handleDrop($event);
        "
        :class="dragging
            ? 'border-primary-500 bg-primary-50 dark:bg-primary-950'
            : 'border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-900'"
        style="
            min-height: 100px;
            padding: 20px;
            border-width: 2px;
            border-style: dashed;
            border-radius: 12px;
            transition: all 0.2s ease;
        "
    >

        {{-- AREA UPLOAD --}}

        <div
            class="text-center"
            style="min-height: 60px;"
        >

            {{-- TEXT --}}

            <p
                class="text-sm font-medium text-gray-700 dark:text-gray-200"
                style="margin: 0;"
            >

                <span x-show="!dragging">
                    Drag & Drop your files or
                    <label
                        for="imagekit-file-input"
                        class="cursor-pointer font-semibold text-primary-600 hover:text-primary-500"
                    >
                        Browse
                    </label>
                </span>

                <span
                    x-show="dragging"
                    x-cloak
                    class="font-semibold text-primary-600"
                >
                    Lepaskan gambar di sini
                </span>

            </p>

            {{-- INPUT FILE --}}

            <input
                id="imagekit-file-input"
                type="file"
                class="hidden"
                accept="image/jpeg,image/png,image/webp"
                @change="handleFileInput($event)"
                :disabled="uploading"
            >

            {{-- STATUS UPLOADING --}}

            <div
                x-show="uploading"
                x-cloak
                class="mt-3"
            >

                <div
                    class="flex justify-between text-xs text-gray-500"
                >

                    <span>
                        Uploading...
                    </span>

                    <span
                        x-text="progress + '%'"
                    ></span>

                </div>

                <div
                    class="mt-1 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"
                >

                    <div
                        class="h-full rounded-full bg-primary-600 transition-all"
                        :style="'width: ' + progress + '%'"
                    ></div>

                </div>

            </div>

        </div>

        {{-- PREVIEW --}}

        <template x-if="preview">

            <div class="mt-4">

                <p
                    class="mb-2 text-xs font-medium text-gray-500"
                >
                    Preview
                </p>

                <div
                    class="flex justify-center"
                >

                    <img
                        :src="preview"
                        alt="Preview Project"
                        style="
                            max-width: 220px;
                            max-height: 140px;
                            width: auto;
                            height: auto;
                            object-fit: contain;
                            border-radius: 8px;
                            border: 1px solid #d1d5db;
                        "
                    >

                </div>

                <p
                    class="mt-2 break-all text-xs text-gray-500"
                    x-text="preview"
                ></p>

            </div>

        </template>

        {{-- INFO --}}

        <p
            class="mt-3 text-xs text-gray-500 dark:text-gray-400"
            style="margin-bottom: 0;"
        >
            Optional. JPG, JPEG, PNG, atau WEBP.
            Maksimal 25 MB.
        </p>

    </div>
</div>