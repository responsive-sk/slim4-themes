# Architecture

This document describes the architecture of the Slim4 Themes package.

## Overview

The Slim4 Themes package is designed to provide a flexible theme system for Slim 4 applications. It allows you to use different template engines (Twig, Latte) and easily switch between themes.

## Components

The package consists of the following components:

### Interfaces

- `ThemeInterface` - Interface for themes.
- `ThemeLoaderInterface` - Interface for theme loaders.
- `ThemeRendererInterface` - Interface for theme renderers.
- `ThemeResponseInterface` - Interface for theme renderers that can render to a response.

### Implementations

#### Twig

- `TwigTheme` - Implementation of `ThemeInterface` for Twig.
- `TwigThemeLoader` - Implementation of `ThemeLoaderInterface` for Twig.
- `TwigThemeRenderer` - Implementation of `ThemeRendererInterface` and `ThemeResponseInterface` for Twig.
- `SlimTwigExtension` - Twig extension for Slim functions.

#### Latte

- `LatteTheme` - Implementation of `ThemeInterface` for Latte.
- `LatteThemeLoader` - Implementation of `ThemeLoaderInterface` for Latte.
- `LatteThemeRenderer` - Implementation of `ThemeRendererInterface` and `ThemeResponseInterface` for Latte.

### Middleware

- `ThemeMiddleware` - Middleware for theme handling.

### Resolver

- `ThemeResolver` - Resolver for themes.

### Attributes

- `Singleton` - Attribute for marking a class as a singleton.

### Exceptions

- `TemplateNotFoundException` - Exception thrown when a template is not found.
- `ThemeNotFoundException` - Exception thrown when a theme is not found.

## Flow

1. The `ThemeMiddleware` is added to the Slim application.
2. When a request is received, the middleware checks for a theme in the query parameters or cookies.
3. If a theme is found, it is loaded using the `ThemeLoaderInterface`.
4. The theme is set in the `ThemeRendererInterface` and added to the request attributes.
5. The request is processed by the application.
6. In the controller, the `ThemeRendererInterface` is used to render a template.
7. The `ThemeRendererInterface` uses the current theme to find the template and render it.
8. The rendered template is returned as a response.

## Theme Structure

A theme consists of the following:

- A name
- A path
- A flag indicating whether it is the default theme
- An optional parent theme
- A configuration

The theme path should contain the following:

- A `templates` directory containing the templates
- An `assets` directory containing the assets (CSS, JavaScript, images, etc.)
- A `theme.json` file containing the theme configuration

Example theme structure:

```
themes/
  default/
    templates/
      layout.twig
      home.twig
      about.twig
    assets/
      css/
        style.css
      js/
        script.js
      images/
        logo.png
    theme.json
  dark/
    templates/
      layout.twig
      home.twig
    assets/
      css/
        style.css
    theme.json
```

Example `theme.json`:

```json
{
  "name": "Default Theme",
  "description": "The default theme for the application",
  "version": "1.0.0",
  "author": "Your Name",
  "parent": null
}
```

## Theme Inheritance

Themes can inherit from other themes. If a template is not found in the current theme, the parent theme is checked. This allows you to create a new theme that only overrides specific templates.

Example:

```json
{
  "name": "Dark Theme",
  "description": "A dark theme for the application",
  "version": "1.0.0",
  "author": "Your Name",
  "parent": "default"
}
```

In this example, the `dark` theme inherits from the `default` theme. If a template is not found in the `dark` theme, the `default` theme is checked.
