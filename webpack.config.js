const path = require('path');

module.exports = {
    entry: './src/task-note/index.js',
    output: {
        path: path.join(__dirname, 'dist/task-note'),
        filename: 'index.js'
    },
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        'presets': [
                            '@babel/preset-env',
                            '@babel/preset-react'
                        ],
                        'plugins': [
                            '@babel/plugin-proposal-class-properties',
                            '@babel/plugin-proposal-function-bind'
                        ]
                    }
                }
            },
            {
                test: /\.css$/,
                use: [ 'style-loader', 'css-loader']
            },
        ]
    },
    externals: {
        jquery: 'jQuery',
        moment: 'moment',
        react: 'React',
        'react-dom': 'ReactDOM',
        underscore: '_',
        wpApiSettings: 'wpApiSettings',
        wp: 'wp'
    },
    devtool: 'source-map',
    mode: 'development'
};
