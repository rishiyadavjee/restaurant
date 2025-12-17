# Image Upload Guide for Restaurant Menu

## Image Storage Location
All product images are stored in: `assets/images/menu/`

## Supported Formats
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)

## File Size Limit
- Maximum file size: 5MB

## Recommended Image Specifications
- **Dimensions**: 800x600 pixels (4:3 aspect ratio)
- **File size**: 100KB - 500KB for optimal loading
- **Format**: JPEG for photos, PNG for graphics with transparency

## How to Add Images

### Method 1: File Upload
1. Go to Menu Management
2. Click "Add New Menu Item" or edit existing item
3. In the "Product Image" section, click "Choose image file..."
4. Select your image file from your computer
5. The image will be automatically uploaded and renamed

### Method 2: Manual Upload
1. Upload your image files to `assets/images/menu/` folder via FTP or file manager
2. In the menu form, enter the exact filename in "Or specify image filename" field
3. Example: `pizza-margherita.jpg`

## Image Naming Convention
- Uploaded images are automatically renamed with format: `menu_[unique_id].[extension]`
- Example: `menu_64b2f8a1c3d4e.jpg`
- This prevents filename conflicts and ensures uniqueness

## Default Image
- If no image is specified, `default.jpg` will be used
- Replace `assets/images/default.jpg` with your preferred default menu item image

## Tips for Best Results
1. **Optimize images** before uploading to reduce file size
2. **Use consistent aspect ratios** for a professional look
3. **Good lighting** makes food photos more appealing
4. **High contrast** helps images stand out on the website
5. **Avoid text overlays** on images as they may not be readable at small sizes

## Troubleshooting
- **Upload fails**: Check file size (max 5MB) and format (JPG/PNG/GIF only)
- **Image not showing**: Verify the filename is correct and file exists in menu folder
- **Poor quality**: Use higher resolution source images (minimum 800px wide)