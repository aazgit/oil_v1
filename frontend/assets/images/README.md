# KishansKraft Image Assets

This directory contains all image assets for the KishansKraft e-commerce platform.

## Directory Structure

```
images/
├── logos/           # Brand logos and icons
├── products/        # Product images
├── banners/         # Hero banners and promotional images
├── icons/           # UI icons and graphics
└── placeholders/    # Placeholder images
```

## Image Guidelines

### Product Images
- **Format**: JPG for photos, PNG for graphics with transparency
- **Size**: Minimum 800x800px, preferably square (1:1 ratio)
- **Quality**: High quality, well-lit, professional photography
- **Naming**: Use descriptive names like `mustard-oil-1l-bottle.jpg`

### Banner Images
- **Format**: JPG preferred
- **Size**: 1920x800px for hero banners
- **Quality**: High resolution for crisp display on all devices
- **Content**: Should complement the brand colors and theme

### Logo Files
- **Format**: SVG preferred, PNG fallback
- **Variants**: Light and dark versions
- **Sizes**: Multiple sizes (16x16, 32x32, 64x64, 128x128, 256x256)

### Icons
- **Format**: SVG preferred for scalability
- **Style**: Consistent line weight and style
- **Size**: 24x24px base size

## Optimization

All images should be optimized for web:
- Compressed for faster loading
- Responsive variants for different screen sizes
- WebP format variants where supported

## Required Images

### Essential Images Needed:
1. **Logo** (`logo.png`, `logo.svg`)
2. **Favicon** (`favicon.ico`, `apple-touch-icon.png`)
3. **Hero Background** (`hero-bg.jpg`)
4. **About Us Image** (`about-us.jpg`)
5. **Product Placeholder** (`placeholder.jpg`)
6. **Open Graph Image** (`og-image.jpg`)

### Product Categories:
- Mustard Oil bottles
- Coconut Oil containers
- Sesame Oil bottles
- Groundnut Oil containers
- Other oil varieties

## Usage in Code

Images are referenced in the codebase using relative paths:
```javascript
// Example usage
const imageUrl = '/frontend/assets/images/products/mustard-oil-1l.jpg';
```

## Notes

- All image paths in the application assume this directory structure
- Placeholder images are used when actual product images are not available
- Images should be served with appropriate caching headers for performance
