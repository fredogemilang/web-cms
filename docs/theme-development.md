# Theme Development Guide

## Overview

Themes in this CMS are stored in the `themes/` directory. Each theme is a self-contained package that includes views, assets, and a configuration file.

## Theme Structure

A typical theme structure looks like this:

```
themes/
  ├── your-theme-slug/
  │   ├── assets/              # CSS, JS, Images, Fonts
  │   │   ├── style.css
  │   │   ├── script.js
  │   │   └── ...
  │   ├── views/               # Blade Templates
  │   │   ├── layouts/
  │   │   │   └── app.blade.php
  │   │   ├── pages/
  │   │   │   └── home.blade.php
  │   │   └── ...
  │   ├── theme.json           # Manifest file
  │   └── screenshot.png       # Theme preview image (800x600 recommended)
```

## theme.json Manifest

Every theme must have a `theme.json` file in its root directory. This file defines the theme's metadata and configuration.

```json
{
    "name": "My Custom Theme",
    "slug": "my-custom-theme",
    "version": "1.0.0",
    "description": "A beautiful custom theme for my website.",
    "author": "John Doe",
    "author_url": "https://example.com",
    "screenshot": "screenshot.png",
    "requires": {
        "php": "^8.1",
        "cms": "^1.0"
    }
}
```

## View Namespace

All views in your theme should be referenced using the theme's slug as a namespace.

Example:
If your theme slug is `iccom`, use `iccom::layouts.app` instead of `themes.iccom.views.layouts.app`.

In your Blade files:
```blade
@extends('iccom::layouts.app')

@include('iccom::components.navbar')
```

## Asset Handling

**Important:** When a theme is activated, the system performs an **Asset Publication** process:

1.  **Assets Directory**: The contents of the `themes/{slug}/assets` directory are **copied** to `public/themes/{slug}/assets`.
2.  **Screenshot**: The `screenshot.png` file is **copied** to `public/themes/{slug}/screenshot.png`.
3.  **Cleanup**: After successful copying, the original `assets` folder and `screenshot.png` in the `themes/` directory are **deleted** to keep the source clean and prevent duplication.

### Referencing Assets

In your Blade templates, always use the `asset()` helper pointing to the public path:

```blade
<link rel="stylesheet" href="{{ asset('themes/my-custom-theme/assets/style.css') }}">
<img src="{{ asset('themes/my-custom-theme/assets/logo.png') }}" alt="Logo">
```

Or in your CSS files (referencing relative images):
```css
.hero {
    background-image: url('banner.jpg'); /* files are in the same folder */
}
```
