var Encore = require('@symfony/webpack-encore');

Encore
// directory where compiled assets will be stored
    .setOutputPath('web/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('manufacturing-dashboard', './assets/entrypoint/manufacturing/dashboard.tsx')
    .addEntry('supplier_order_list_table', './assets/entrypoint/supplier/cmDashboard.tsx')
    .addEntry('purchase_order_allocate', './assets/entrypoint/manufacturing/chooseOtherAllocations.tsx')
    .addEntry('to_allocate_to_order_purchase_order', './assets/entrypoint/manufacturing/toAllocateCandidates.tsx')
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .enableReactPreset()
    .enableTypeScriptLoader()
    .enableLessLoader()

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
