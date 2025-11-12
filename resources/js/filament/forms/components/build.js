import esbuild from 'esbuild';

esbuild.build({
    entryPoints: ['resources/js/filament/forms/components/signature-pad.js'],
    bundle: true,
    outfile: 'resources/js/filament/forms/components/signature-pad.bundle.js',
    format: 'iife',
    minify: true,
    external: [],
}).then(() => {
    console.log('✓ Signature pad component built successfully');
}).catch(() => process.exit(1));
