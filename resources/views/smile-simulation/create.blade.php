<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محاكاة الابتسامة | MedAssist</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f7fa;
            padding: 40px 16px;
            text-align: center;
        }

        .card {
            max-width: 480px;
            margin: auto;
            padding: 28px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px #00000012;
        }

        input,
        button {
            margin-top: 18px;
            max-width: 100%;
        }

        button {
            background: #087e8b;
            color: white;
            border: 0;
            border-radius: 8px;
            padding: 12px 24px;
            cursor: pointer;
        }

        #imagePreviewContainer {
            display: none;
            margin-top: 25px;
        }

        #beforePreview {
            width: 100%;
            max-width: 350px;
            border-radius: 12px;
        }

        .comparison-container {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .image-box {
            min-width: 0;
            padding: 12px;
            background: #f3f7fa;
            border-radius: 12px;
        }

        .image-box img {
            width: 100%;
            height: 180px;
            object-fit: contain;
            border-radius: 10px;
        }

        #afterPlaceholder {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #b5cbd0;
            border-radius: 10px;
            color: #667b80;
        }

        #afterPreview[hidden] {
            display: none;
        }

        @media (max-width: 480px) {
            .comparison-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="card">

        <h1>محاكاة الابتسامة</h1>

        <p>ارفع صورة واضحة لأسنانك لبدء المحاكاة.</p>

        <form action="{{ route('smile.upload') }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            <input
                type="file"
                id="beforeImageInput"
                name="before_image"
                accept="image/jpeg,image/png,image/webp"
                required>

            <div id="imagePreviewContainer">
                <h3>نتيجة محاكاة الابتسامة</h3>

                <div class="comparison-container">
                    <div class="image-box">
                        <h4>Before - قبل</h4>
                        <img id="beforePreview" alt="الصورة الأصلية">
                    </div>

                    <div class="image-box">
                        <h4>After - بعد</h4>
                        <div id="afterPlaceholder">
                            <p>ستظهر نتيجة الذكاء الاصطناعي هنا</p>
                        </div>
                        <img id="afterPreview"
                            alt="الصورة بعد المحاكاة"
                            hidden>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit">
                    رفع الصورة
                </button>
            </div>

        </form>

        <p>
            النتائج محاكاة تقريبية وليست ضماناً لنتيجة علاجية.
        </p>

    </div>

    <script>
        const imageInput = document.getElementById('beforeImageInput');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const beforePreview = document.getElementById('beforePreview');

        imageInput.addEventListener('change', function() {

            const file = this.files[0];

            if (!file) {
                previewContainer.style.display = 'none';
                beforePreview.removeAttribute('src');
                return;
            }

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!allowedTypes.includes(file.type)) {
                alert('يرجى اختيار صورة JPG أو PNG أو WEBP');
                this.value = '';
                previewContainer.style.display = 'none';
                beforePreview.removeAttribute('src');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('حجم الصورة يجب ألا يتجاوز 5MB');
                this.value = '';
                previewContainer.style.display = 'none';
                beforePreview.removeAttribute('src');
                return;
            }

            const reader = new FileReader();

            reader.onload = function(event) {
                beforePreview.src = event.target.result;
                previewContainer.style.display = 'block';
            };

            reader.readAsDataURL(file);

        });

        const afterPreview = document.getElementById('afterPreview');
        const afterPlaceholder = document.getElementById('afterPlaceholder');

        function showAfterImage(imageUrl) {
            afterPreview.onload = function() {
                afterPlaceholder.style.display = 'none';
                afterPreview.hidden = false;
            };

            afterPreview.onerror = function() {
                afterPreview.hidden = true;
                afterPlaceholder.style.display = 'flex';
                afterPlaceholder.querySelector('p').textContent =
                    'تعذر تحميل نتيجة المحاكاة';
            };

            afterPreview.src = imageUrl;
        }
    </script>

</body>

</html>