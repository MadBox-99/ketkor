import SignaturePad from 'signature_pad';

function signaturePadFormComponent({ state, backgroundColor, penColor }) {
    return {
        state: state,
        signaturePad: null,

        init() {
            const canvas = this.$refs.canvas;

            if (!canvas) {
                console.error('Canvas element not found');
                return;
            }

            // Detect dark mode
            const isDarkMode = document.documentElement.classList.contains('dark');
            const bg = backgroundColor || (isDarkMode ? 'rgb(55, 65, 81)' : 'rgb(255, 255, 255)');
            const pen = penColor || (isDarkMode ? 'rgb(255, 255, 255)' : 'rgb(0, 0, 0)');

            // Initialize signature pad
            this.signaturePad = new SignaturePad(canvas, {
                backgroundColor: bg,
                penColor: pen,
            });

            // Set canvas size
            this.resizeCanvas();
            window.addEventListener('resize', () => this.resizeCanvas());

            // Load existing signature if available
            if (this.state) {
                try {
                    this.signaturePad.fromDataURL(this.state);
                } catch (error) {
                    console.error('Failed to load signature:', error);
                }
            }

            // Save signature when drawing ends
            canvas.addEventListener('mouseup', () => this.saveSignature());
            canvas.addEventListener('touchend', () => this.saveSignature());
        },

        resizeCanvas() {
            const canvas = this.$refs.canvas;
            const ratio = Math.max(window.devicePixelRatio || 1, 1);

            // Store current signature data if exists
            const signatureData = !this.signaturePad?.isEmpty() ? this.signaturePad.toData() : null;

            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);

            // Restore signature after resize
            if (signatureData) {
                this.signaturePad.fromData(signatureData);
            } else {
                this.signaturePad?.clear();
            }
        },

        saveSignature() {
            if (!this.signaturePad.isEmpty()) {
                this.state = this.signaturePad.toDataURL();
            }
        },

        clear() {
            this.signaturePad?.clear();
            this.state = null;
        },
    };
}

// Make it globally available for Alpine
window.signaturePadFormComponent = signaturePadFormComponent;
