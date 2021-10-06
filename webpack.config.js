const path = require('path');
const webpack = require('webpack');


//  Plugins
const globby = require('globby');
const CopyPlugin = require('copy-webpack-plugin');
const TerserJSPlugin = require('terser-webpack-plugin');

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
        filename:'js/cookie.js',
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
            },
          ],
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
      target: ['web', 'es5'],
      output: {
        publicPath: '/',
        path: getPublicPath(),
        filename: 'js/cookie-ie.js',
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
            },
            {
              from: getSourcePath('css/cookie-ie.css'),
              to: getPublicPath('css/cookie-ie.css'),
            },
          ],
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
  ];
};

function getSourcePath() {
  return path.resolve(process.env.npm_package_config_path_src, ...arguments);
}

function getPublicPath() {
  return path.resolve(process.env.npm_package_config_path_public, ...arguments);
}
