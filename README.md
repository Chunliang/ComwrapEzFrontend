# Comwrap Ez Frontend Bundle

Symfony Bundle to handle Comwrap Ez Frontend Components and Assets integrations

## Getting Started

### Requirements

PHP 7.1.3+, Symfony 3.4+, eZ Platform 2.5+,  eZ Platform 3.x

### Installing in Symfony 3.4+

Install with composer:

```
composer require comwrapez/ez-frontend-bundle:dev-master
```

Enable the bundles in `app/AppKernel.php`

```
$bundles = [
    ...
    new Symfony\WebpackEncoreBundle\WebpackEncoreBundle(),
    new Comwrap\Bundle\ComwrapEzFrontendBundle\ComwrapEzFrontendBundle(),
];
```

Add the following configs in `app/config/config.yml`

```
webpack_encore:
    output_path: "%kernel.project_dir%/web/build"
    builds:
        comwrap_ez_frontend: "%kernel.project_dir%/web/assets/frontend/build"

comwrap_ez_frontend:
    frontend:
        source: '%frontend_source_path%'
        destination: '%backend_components_path%'
        assets: '%backend_assets_path%'
```

Add the following values in `app/config/parameters.yml`

```
parameters:
    ...
    frontend_source_path: PATH_TO_FRONTEND
    backend_components_path: PATH_TO_BACKEND_COMPONENTS
    backend_assets_path: web
```

Run this command to create/update frontend assets in backend

```
bin/console comwrap:frontend:init
```
 
to accomplish these tasks:
 
- Merge Node JS dependencies from frontend into backend and run `yarn install` to update `node_modules`
- Generate Encore Webpack configuration files and add to the encore webpack config file `webpack.config.js` 
- Generate/copy Frontend assets (CSS,JS,Fonts,Images) in backend directory `web/assets/frontend/build`
- Get style and script tags for page Twig layout

### Update Frontend assets

```
yarn encore dev --config-name comwrap_ez_frontend
```


### Installing in Symfony 4.4+

Install with composer:

```
composer require comwrapez/ez-frontend-bundle:dev-master
```

Run yarn install:

```
yarn install
```

Add the following configs in `config/packages/webpack_encore.yaml`

```
webpack_encore:
    output_path: "%kernel.project_dir%/public/build"
    builds:
        comwrap_ez_frontend: "%kernel.project_dir%/public/assets/frontend/build"

comwrap_ez_frontend:
    frontend:
        source: '%frontend_source_path%'
        destination: '%backend_components_path%'
        assets: '%backend_assets_path%'
```

Add the following values in `config/services.yaml`

```
parameters:
    ...
    frontend_source_path: PATH_TO_FRONTEND
    backend_components_path: PATH_TO_BACKEND_COMPONENTS
    backend_assets_path: public
```

Run this command to create/update frontend assets in backend

```
bin/console comwrap:frontend:init
```
 
to accomplish these tasks:
 
- Merge Node JS dependencies from frontend into backend and run `yarn install` to update `node_modules`
- Generate Encore Webpack configuration files and add to the encore webpack config file `webpack.config.js` 
- Generate/copy Frontend assets (CSS,JS,Fonts,Images) in backend directory `public/assets/frontend/build`
- Get style and script tags for page Twig layout

### Update Frontend assets

```
yarn encore dev --config-name comwrap_ez_frontend
```