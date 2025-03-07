import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import { terser } from 'rollup-plugin-terser';

export default {
    input: 'src/index.js',
    output: [
        {
            file: 'dist/simplebase.js',
            format: 'umd',
            name: 'SimpleBase',
            sourcemap: true,
        },
        {
            file: 'dist/simplebase.min.js',
            format: 'umd',
            name: 'SimpleBase',
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