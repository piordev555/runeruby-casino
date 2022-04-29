const mix = require('laravel-mix');
const WebpackShellPlugin = require('webpack-shell-plugin'),
      CompressionPlugin = require('compression-webpack-plugin'),
      OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');

mix.extend('addWebpackLoaders', (webpackConfig, loaderRules) => {
    loaderRules.forEach((loaderRule) => webpackConfig.module.rules.push(loaderRule));
});

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/admin/app.scss', 'public/css/admin')
    .sass('resources/sass/error.scss', 'public/css')
    .copy('resources/img', 'public/img')
    .copy('resources/webfonts', 'public/webfonts')
    .copy('resources/sounds', 'public/sounds')
    .options({
        processCssUrls: false
    })
    .version()
    .webpackConfig({
        plugins: [
            new WebpackShellPlugin({
                onBuildStart: ['php artisan cache:clear --quiet'],
                onBuildEnd: []
            }),
            new OptimizeCssAssetsPlugin({
                assetNameRegExp: /\.optimize\.css$/g,
                cssProcessor: require('cssnano'),
                cssProcessorPluginOptions: {
                    preset: ['default', { discardComments: { removeAll: true } }],
                },
                canPrint: true
            }),
            new CompressionPlugin({
                filename: '[path].gz[query]',
                algorithm: 'gzip',
                test: /\.js$|\.css$|\.html$|\.eot?.+$|\.ttf?.+$|\.woff?.+$|\.svg?.+$/,
                threshold: 10240,
                minRatio: 0.8,
                cache: false
            })
        ]
    })
    .addWebpackLoaders([
        {
            test: /\.key|\.txt$/i,
            use: "raw-loader"
        }
    ]);
