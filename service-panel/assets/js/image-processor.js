/**
 * Image Processor Utility
 * Handles client-side watermarking and timestamping
 */

const ImageProcessor = {
    process: async (file, watermarkText = "Infinity Computer") => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (event) => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    // Set canvas dimensions
                    canvas.width = img.width;
                    canvas.height = img.height;

                    // Draw original image
                    ctx.drawImage(img, 0, 0);

                    // Configure Text Styles
                    const baseSize = canvas.width / 30;
                    ctx.shadowColor = "rgba(0, 0, 0, 0.5)";
                    ctx.shadowBlur = 4;
                    ctx.shadowOffsetX = 2;
                    ctx.shadowOffsetY = 2;

                    // 1. Watermark (Diagonal Center)
                    ctx.save();
                    ctx.translate(canvas.width / 2, canvas.height / 2);
                    ctx.rotate(-25 * Math.PI / 180);
                    
                    const fontSize = canvas.width / 10;
                    ctx.font = `bold ${fontSize}px Arial`;
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";

                    // Draw Stroke (Subtle Outline)
                    ctx.strokeStyle = "rgba(0, 0, 0, 0.4)";
                    ctx.lineWidth = Math.max(2, fontSize / 25);
                    ctx.strokeText(watermarkText, 0, 0);

                    // Draw Fill (Transparent)
                    ctx.fillStyle = "rgba(255, 255, 255, 0.45)";
                    ctx.fillText(watermarkText, 0, 0);
                    ctx.restore();

                    // 2. Timestamp (Bottom-Right)
                    const timestamp = new Date().toLocaleString('sv-SE').replace('T', ' '); 
                    const tsSize = Math.max(20, canvas.width / 30); // Increased Size
                    ctx.font = `bold ${tsSize}px Arial`;
                    ctx.textAlign = "right";

                    const tx = canvas.width - 30;
                    const ty = canvas.height - 30;

                    // Stroke for timestamp
                    ctx.strokeStyle = "rgba(0, 0, 0, 0.9)";
                    ctx.lineWidth = Math.max(3, tsSize / 10);
                    ctx.strokeText(timestamp, tx, ty);

                    // Fill for timestamp
                    ctx.fillStyle = "white";
                    ctx.fillText(timestamp, tx, ty);

                    // Convert to Blob
                    canvas.toBlob((blob) => {
                        resolve(blob);
                    }, 'image/jpeg', 0.85);
                };
                img.onerror = reject;
            };
            reader.onerror = reject;
        });
    },

    setupPreview: (inputSelector, previewContainerSelector, showPreview = true) => {
        const inputs = document.querySelectorAll(inputSelector);
        const container = document.querySelector(previewContainerSelector);
        if (inputs.length === 0 || !container) return;

        inputs.forEach(input => {
            const isCameraInput = input.hasAttribute('capture');
            const label = input.parentElement;

            // Handle Desktop Camera Click
            if (isCameraInput) {
                label.addEventListener('click', (e) => {
                    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
                    if (!isMobile) {
                        e.preventDefault();
                        ImageProcessor.openCameraModal(async (blob) => {
                            container.innerHTML = '<p style="color:var(--primary)">Processing captured image...</p>';
                            try {
                                const processedBlob = await ImageProcessor.process(blob);
                                window.lastProcessedBlob = processedBlob;
                                ImageProcessor.displayFeedback(container, processedBlob, showPreview);
                            } catch (err) {
                                container.innerHTML = '<p style="color:red">Failed to process image.</p>';
                            }
                        });
                    }
                });
            }

            input.addEventListener('change', async (e) => {
                const file = e.target.files[0];
                if (!file) return;

                inputs.forEach(other => { if (other !== input) other.value = ''; });

                container.innerHTML = '<p style="color:var(--primary)">Processing image...</p>';
                try {
                    const processedBlob = await ImageProcessor.process(file);
                    window.lastProcessedBlob = processedBlob; // Store globally for form submission
                    ImageProcessor.displayFeedback(container, processedBlob, showPreview);
                } catch (err) {
                    container.innerHTML = '<p style="color:red">Failed to process image.</p>';
                }
            });
        });
    },

    displayFeedback: (container, blob, showPreview) => {
        if (showPreview) {
            const url = URL.createObjectURL(blob);
            container.innerHTML = `
                <div style="margin-top:15px; border:2px dashed var(--primary); padding:10px; border-radius:10px;">
                    <p style="font-size:0.8rem; font-weight:600; color:var(--primary); margin-bottom:10px; text-align: center;">PREVIEW (Watermark & Timestamp Applied)</p>
                    <div style="display: flex; justify-content: center;">
                        <img src="${url}" style="width:200px; height:200px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); object-fit: cover; border: 1px solid #e2e8f0;">
                    </div>
                </div>
            `;
        } else {
            container.innerHTML = `
                <div style="margin-top:15px; border:2px solid #28a745; padding:15px; border-radius:10px; background-color: #d4edda; color: #155724; display: flex; align-items: center; gap: 10px; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span style="font-weight: 600;">Image successfully captured and processed!</span>
                </div>
            `;
        }
    },

    openCameraModal: async (onCapture) => {
        const modal = document.createElement('div');
        modal.style = "position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:10000; display:flex; align-items:center; justify-content:center; padding:15px; font-family:sans-serif;";
        modal.innerHTML = `
            <div style="background:white; padding:25px; border-radius:20px; max-width:500px; width:100%; position:relative; box-shadow:0 20px 50px rgba(0,0,0,0.3);">
                <button id="closeCam" style="position:absolute; top:15px; right:15px; border:0; background:#eee; width:30px; height:30px; border-radius:50%; cursor:pointer; font-weight:bold;">&times;</button>
                <h3 style="margin-top:0; margin-bottom:15px; color:#1f2a37; text-align:center;">Desktop Camera</h3>
                <video id="camVideo" autoplay playsinline style="width:100%; border-radius:12px; background:#000; aspect-ratio: 4/3; object-fit: cover;"></video>
                <div style="display:flex; gap:12px; margin-top:20px;">
                    <button id="captureBtn" style="flex:2; background:#1f5fae; color:white; border:0; padding:12px; border-radius:10px; font-weight:600; cursor:pointer;">Capture Photo</button>
                    <button id="cancelCam" style="flex:1; background:#f3f4f6; color:#4b5563; border:0; padding:12px; border-radius:10px; font-weight:600; cursor:pointer;">Cancel</button>
                </div>
                <canvas id="camCanvas" style="display:none;"></canvas>
            </div>
        `;
        document.body.appendChild(modal);

        const video = modal.querySelector('#camVideo');
        const canvas = modal.querySelector('#camCanvas');
        let stream = null;

        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 1280 }, height: { ideal: 720 } } });
            video.srcObject = stream;
        } catch (err) {
            alert("Unable to access camera. Please check permissions or ensure no other app is using it.");
            document.body.removeChild(modal);
            return;
        }

        modal.querySelector('#captureBtn').onclick = () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            canvas.toBlob(blob => {
                onCapture(blob);
                if (stream) stream.getTracks().forEach(t => t.stop());
                document.body.removeChild(modal);
            }, 'image/jpeg', 0.9);
        };

        const close = () => {
            if (stream) stream.getTracks().forEach(t => t.stop());
            document.body.removeChild(modal);
        };
        modal.querySelector('#closeCam').onclick = close;
        modal.querySelector('#cancelCam').onclick = close;
    },

    /**
     * Optional check if any camera is available
     */
    initCameraVisibility: async (selector = '.camera-btn') => {
        const btns = document.querySelectorAll(selector);
        if (btns.length === 0) return;

        const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
        
        // On Desktop, we only check if navigator.mediaDevices exists
        if (!isMobile && !navigator.mediaDevices) {
            btns.forEach(b => b.style.display = 'none');
        }
    }
};
