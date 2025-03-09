import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import { terser } from 'rollup-plugin-terser';

export default {
    input: 'src/index.js',
    output: [
        {
            file: 'dist/humbleBase.js',
            format: 'umd',
            name: 'humbleBase',
            sourcemap: true,
        },
        {
            file: 'dist/humbleBase.min.js',
            format: 'umd',
            name: 'humbleBase',
            plugins: [terser()],
            sourcemap: true,
        },
    ],
    plugins: [
        resolve(),
        commonjs(),
    ],
    watch: {
        include: 'src/**',
    },
};