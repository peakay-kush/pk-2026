# Tutorial Resources Guide

## Overview
The tutorials system now supports three types of resources:
1. **Video Links** - YouTube or other video URLs
2. **PDF Files** - Downloadable PDF documents
3. **Images** - Tutorial screenshots and diagrams

## How to Add Resources to Tutorials

### 1. Using Database Management

You can add resources directly to the database. Here's the structure:

#### Video URLs (video_url column)
- Single video: `https://www.youtube.com/watch?v=VIDEO_ID`
- Multiple videos (comma-separated): `https://www.youtube.com/watch?v=VIDEO_ID1, https://www.youtube.com/watch?v=VIDEO_ID2`
- The system will automatically embed YouTube videos

#### PDF Files (pdf_file column)
- Upload your PDF files to: `assets/pdfs/`
- Single file: `tutorial-guide.pdf`
- Multiple files (comma-separated): `tutorial-guide.pdf, reference-sheet.pdf, schematic.pdf`

#### Images (images column)
- Upload your images to: `assets/images/tutorials/`
- Single image: `circuit-diagram.jpg`
- Multiple images (comma-separated): `circuit-diagram.jpg, breadboard-layout.png, final-result.jpg`

### 2. Example SQL Update

```sql
UPDATE tutorials 
SET 
    video_url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    pdf_file = 'arduino-guide.pdf, component-list.pdf',
    images = 'step1.jpg, step2.jpg, step3.jpg'
WHERE slug = 'getting-started-arduino';
```

### 3. Directory Structure

```
pk 2026/
├── assets/
│   ├── pdfs/              ← Put your PDF files here
│   │   ├── tutorial-1.pdf
│   │   └── guide.pdf
│   └── images/
│       └── tutorials/     ← Put your tutorial images here
│           ├── diagram1.jpg
│           ├── circuit.png
│           └── result.jpg
```

### 4. What Happens When Users Click "Read Tutorial"

When users click "Read Tutorial" on any tutorial card, they will be directed to a detailed page that shows:

1. **Tutorial Content** - The main text/HTML content
2. **Video Resources** - Embedded YouTube videos or links to other videos
3. **PDF Documents** - Download and view buttons for all attached PDFs
4. **Tutorial Images** - Gallery of images that can be clicked to view full size

### 5. Image Placeholders Removed

- Tutorial cards on the tutorials page no longer show placeholder images
- Tutorial cards on the home page no longer show placeholder images
- The focus is now on the content and resources you provide

### 6. Adding Resources to Existing Tutorials

To add resources to an existing tutorial, you have two options:

**Option A: Using a Database Tool**
1. Download DB Browser for SQLite
2. Open `database/tech_electronics.db`
3. Navigate to the tutorials table
4. Edit the desired tutorial row
5. Add your video URLs, PDF filenames, and image filenames
6. Save changes

**Option B: Using PHP Script**
Create a PHP script to update tutorials programmatically:

```php
<?php
require_once 'includes/db.php';

// Update a specific tutorial
$stmt = $conn->prepare("UPDATE tutorials SET video_url = ?, pdf_file = ?, images = ? WHERE slug = ?");
$stmt->execute([
    'https://www.youtube.com/watch?v=VIDEO_ID',  // video_url
    'tutorial-guide.pdf, reference.pdf',          // pdf_file
    'step1.jpg, step2.jpg, step3.jpg',           // images
    'getting-started-arduino'                     // slug
]);
?>
```

## Important Notes

1. **File Uploads**: Currently, you need to manually upload files to the correct directories via FTP or file manager
2. **YouTube URLs**: The system automatically detects and embeds YouTube videos
3. **Comma Separation**: When adding multiple videos, PDFs, or images, separate them with commas
4. **File Paths**: 
   - PDFs: Just the filename (e.g., `guide.pdf`)
   - Images: Just the filename (e.g., `diagram.jpg`)
   - Videos: Full URLs (e.g., `https://www.youtube.com/watch?v=...`)

## Future Enhancements (Optional)

Consider adding:
- Admin panel for easy resource management
- File upload interface
- Drag-and-drop file uploads
- Video thumbnail preview
- Image gallery with lightbox

## Need Help?

If you need to add resources to tutorials programmatically or set up an admin interface, let me know!
