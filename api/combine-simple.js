const { PDFDocument } = require('pdf-lib');
const multer = require('multer');
const cors = require('cors');

// Configure multer for file uploads
const upload = multer({
  storage: multer.memoryStorage(),
  limits: {
    fileSize: 10 * 1024 * 1024, // 10MB limit
  },
  fileFilter: (req, file, cb) => {
    if (file.mimetype === 'application/pdf') {
      cb(null, true);
    } else {
      cb(new Error('Only PDF files are allowed'), false);
    }
  }
});

// Configure CORS
const corsMiddleware = cors({
  origin: '*',
  methods: ['POST'],
  allowedHeaders: ['Content-Type']
});

export default async function handler(req, res) {
  corsMiddleware(req, res, () => {});

  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  upload.single('pdfFile')(req, res, async (err) => {
    if (err) {
      return res.status(400).json({ success: false, error: err.message });
    }

    if (!req.file) {
      return res.status(400).json({ success: false, error: 'No file uploaded' });
    }

    try {
      console.log('Starting simple PDF processing...');
      
      // Load the PDF
      const pdfDoc = await PDFDocument.load(req.file.buffer);
      const pageCount = pdfDoc.getPageCount();

      console.log('PDF loaded, page count:', pageCount);

      if (pageCount === 0) {
        throw new Error('Invalid PDF file or empty PDF');
      }

      // Create new PDF document
      const newPdfDoc = await PDFDocument.create();

      // Alternative approach: copy pages using different method
      console.log('Copying pages using alternative method...');
      
      // Get all pages from original document
      const pages = pdfDoc.getPages();
      console.log('Got pages array, length:', pages.length);
      
      for (let i = 0; i < pages.length; i++) {
        console.log(`Processing page ${i + 1} of ${pages.length}`);
        
        try {
          // Get the page
          const page = pages[i];
          console.log(`Page ${i + 1} size:`, page.getSize());
          
          // Copy page using copyPages method
          const [copiedPage] = await newPdfDoc.copyPages(pdfDoc, [i]);
          console.log(`Copied page ${i + 1}, size:`, copiedPage.getSize());
          
          // Add the copied page to new document
          newPdfDoc.addPage(copiedPage);
          
          console.log(`Successfully added page ${i + 1} to new document`);
          
        } catch (pageError) {
          console.error(`Error processing page ${i + 1}:`, pageError);
          // Skip this page and continue
        }
      }

      console.log('All pages processed, generating PDF...');

      // Generate PDF bytes
      const pdfBytes = await newPdfDoc.save();

      console.log('PDF generated, size:', pdfBytes.length, 'bytes');

      // Generate unique filename
      const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
      const filename = `simple_${timestamp}_${Math.random().toString(36).substr(2, 9)}.pdf`;

      // Convert to base64 properly
      const base64Data = Buffer.from(pdfBytes).toString('base64');

      console.log('Base64 conversion completed, length:', base64Data.length);

      // Return success response
      res.status(200).json({
        success: true,
        message: 'PDF berhasil digabungkan! (Simple Version)',
        filename: filename,
        pageCount: pageCount,
        layout: '1x1 (Simple Copy)',
        downloadUrl: `/api/download?file=${encodeURIComponent(filename)}`,
        pdfData: base64Data
      });

    } catch (error) {
      console.error('PDF processing error:', error);
      res.status(400).json({
        success: false,
        error: `PDF processing failed: ${error.message}`
      });
    }
  });
}
