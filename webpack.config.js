const path = require('path');
const webpack = require('webpack');

const dotenv = require('dotenv').config({ path: __dirname + '/.env' });

//  Plugins
const globby = require('globby');
const CopyPlugin = require('copy-webpack-plugin');
const TerserJSPlugin = require('terser-webpack-plugin');
const Dotenv = require('dotenv-webpack');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = (env, options) => {
    const isDevelopment = env.NODE_ENV === 'development';
    return [
        {
            mode: env.NODE_ENV,
            entry: {
                site: getSourcePath('js/main.ts'),
            },
            output: {
                publicPath: '/',
                path: getPublicPath(),
                filename: isDevelopment ? 'js/cookie.js' : 'js/cookie.[contenthash].js',
            },
            resolve: {
                extensions: ['*', '.tsx', '.ts', '.js', '.json'],
                alias: {
                    'wicg-inert': path.resolve('./node_modules/wicg-inert/dist/inert'),
                },
            },
            devtool: false,
            module: {
                rules: [
                    {
                        test: /\.tsx?$/,
                        use: 'ts-loader',
                        exclude: /node_modules/,
                    },
                ],
            },

            // @ts-ignore
            plugins: [
                new CopyPlugin({
                    patterns: [
                        {
                            from: getSourcePath('css/inert.css'),
                            to: getPublicPath('css/inert.css'),
                        }
                    ],
                }),
                new Dotenv(),
                ...(!options.watch
                    ? [
                        new HtmlWebpackPlugin({
                            files: {
                                js: 'js/[name].[contenthash].js',
                            },
                        }),
                    ]
                    : []),
                new CleanWebpackPlugin({
                    // dry: true,
                    // verbose: true,
                    cleanOnceBeforeBuildPatterns: ['js/**/*', 'css/**/*', '!css/inert.css', '!css/ie.**.css', '!js/ie.**.js'],
                }),
            ],
            optimization: {
                minimizer: [
                    new TerserJSPlugin({
                        terserOptions: {
                            output: {
                                comments: false,
                            },
                        },
                    }),
                ],
            },
            stats: 'normal',
        },
        /**************************
         * IE 11 CSS and JS config
         **************************/
        {
            mode: env.NODE_ENV,
            entry: {
                site: getSourcePath('js/ie.ts'),
            },
            output: {
                publicPath: '/',
                path: getPublicPath(),
                filename: isDevelopment ? 'js/cookie-ie.js' : 'js/cookie-ie.[contenthash].js',
            },
            resolve: {
                extensions: ['*', '.tsx', '.ts', '.js', '.json'],
                alias: {
                    'wicg-inert': path.resolve('./node_modules/wicg-inert/dist/inert'),
                },
            },
            devtool: false,
            module: {
                rules: [
                    {
                        test: /\.tsx?$/,
                        use: [
                            {
                                loader: 'ts-loader',
                                options: {
                                    configFile: 'tsconfig.ie.json',
                                },
                            },
                        ],
                        exclude: /node_modules/,
                    },
                ],
            },

            // @ts-ignore
            plugins: [
                new CopyPlugin({
                    patterns: [
                        {
                            from: getSourcePath('css/inert.css'),
                            to: getPublicPath('css/inert.css'),
                        }
                    ],
                }),
                new Dotenv(),
                ...(!options.watch
                    ? [
                        new HtmlWebpackPlugin({
                            files: {
                                js: 'js/[name].[contenthash].js',
                            },
                        }),
                    ]
                    : []),
                new CleanWebpackPlugin({
                    // dry: true,
                    // verbose: true,
                    cleanOnceBeforeBuildPatterns: ['js/**/*', 'css/**/*', '!css/inert.css', '!css/ie.**.css', '!js/ie.**.js'],
                }),
            ],
            optimization: {
                minimizer: [
                    new TerserJSPlugin({
                        terserOptions: {
                            output: {
                                comments: false,
                            },
                        },
                    }),
                ],
            },
            stats: 'normal',
        }
    ];
};

function getSourcePath() {
    return path.resolve(process.env.npm_package_config_path_src, ...arguments);
}

function getPublicPath() {
    return path.resolve(process.env.npm_package_config_path_public, ...arguments);
}
