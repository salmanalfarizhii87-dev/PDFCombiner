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

// Paper size configurations (in points)
const PAPER_SIZES = {
  A4: { width: 595, height: 842 },
  Letter: { width: 612, height: 792 },
  Legal: { width: 612, height: 1008 },
  A3: { width: 842, height: 1191 },
  A5: { width: 420, height: 595 },
  B4: { width: 708, height: 1001 },
  B5: { width: 499, height: 708 }
};

// Calculate layout based on pages per sheet and arrangement
function calculateLayout(pagesPerSheet, pageArrangement = 'side_by_side') {
  switch (pagesPerSheet) {
    case 2:
      return pageArrangement === 'top_bottom' ? { rows: 2, cols: 1 } : { rows: 1, cols: 2 };
    case 4:
      return pageArrangement === 'top_bottom' ? { rows: 4, cols: 1 } : { rows: 2, cols: 2 };
    case 5:
      return pageArrangement === 'top_bottom' ? { rows: 5, cols: 1 } : { rows: 2, cols: 3 };
    case 6:
      return pageArrangement === 'top_bottom' ? { rows: 6, cols: 1 } : { rows: 2, cols: 3 };
    case 8:
      return pageArrangement === 'top_bottom' ? { rows: 8, cols: 1 } : { rows: 2, cols: 4 };
    default:
      return pageArrangement === 'top_bottom' ? { rows: 2, cols: 1 } : { rows: 2, cols: 2 };
  }
}

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

    const { pagesPerSheet = 4, pageArrangement = 'side_by_side', paperSize = 'A4' } = req.body;

    try {
      console.log('Starting PDF processing...');
      
      // Load the PDF
      const pdfDoc = await PDFDocument.load(req.file.buffer);
      const pageCount = pdfDoc.getPageCount();

      console.log('PDF loaded, page count:', pageCount);

      if (pageCount === 0) {
        throw new Error('Invalid PDF file or empty PDF');
      }

      // Calculate layout
      const { rows, cols } = calculateLayout(parseInt(pagesPerSheet), pageArrangement);
      console.log('Layout calculated:', { rows, cols });
      
      // Get paper dimensions
      const { width: pageWidth, height: pageHeight } = PAPER_SIZES[paperSize] || PAPER_SIZES.A4;
      console.log('Paper dimensions:', { pageWidth, pageHeight });
      
      // Calculate individual page size
      const cellWidth = pageWidth / cols;
      const cellHeight = pageHeight / rows;
      console.log('Cell dimensions:', { cellWidth, cellHeight });

      // Create new PDF document
      const newPdfDoc = await PDFDocument.create();

      // Process pages
      let pageIndex = 0;

      while (pageIndex < pageCount) {
        console.log(`Processing page group starting from page ${pageIndex}`);
        
        // Add new page
        const newPage = newPdfDoc.addPage([pageWidth, pageHeight]);

        // Place pages on current sheet
        for (let row = 0; row < rows && pageIndex < pageCount; row++) {
          for (let col = 0; col < cols && pageIndex < pageCount; col++) {
            console.log(`Processing page ${pageIndex} at position (${row}, ${col})`);
            
            try {
              // Copy page using copyPages method
              const [embeddedPage] = await newPdfDoc.copyPages(pdfDoc, [pageIndex]);
              
              // Get original page size
              const { width: originalWidth, height: originalHeight } = embeddedPage.getSize();
              console.log(`Original page size: ${originalWidth}x${originalHeight}`);

              // Calculate scale to fit
              const scaleX = cellWidth / originalWidth;
              const scaleY = cellHeight / originalHeight;
              const scale = Math.min(scaleX, scaleY);
              console.log(`Scale calculated: ${scale}`);

              // Calculate position (PDF coordinates start from bottom-left)
              const x = col * cellWidth;
              const y = pageHeight - (row + 1) * cellHeight;

              // Calculate centered position
              const scaledWidth = originalWidth * scale;
              const scaledHeight = originalHeight * scale;
              const centeredX = x + (cellWidth - scaledWidth) / 2;
              const centeredY = y + (cellHeight - scaledHeight) / 2;

              console.log(`Drawing page at position: (${centeredX}, ${centeredY}) with size: ${scaledWidth}x${scaledHeight}`);

              // Draw the embedded page - try simpler approach first
              newPage.drawPage(embeddedPage, {
                x: x,
                y: y,
                width: scaledWidth,
                height: scaledHeight,
              });

              console.log(`Successfully drew page ${pageIndex}`);

            } catch (pageError) {
              console.error(`Error processing page ${pageIndex}:`, pageError);
              // Skip this page and continue
            }

            pageIndex++;
          }
        }
      }

      console.log('All pages processed, generating PDF...');

      // Generate PDF bytes
      const pdfBytes = await newPdfDoc.save();

      console.log('PDF generated, size:', pdfBytes.length, 'bytes');

      // Generate unique filename
      const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
      const filename = `combined_${timestamp}_${Math.random().toString(36).substr(2, 9)}.pdf`;

      // Convert to base64 properly
      const base64Data = Buffer.from(pdfBytes).toString('base64');

      console.log('Base64 conversion completed, length:', base64Data.length);

      // Return success response
      res.status(200).json({
        success: true,
        message: 'PDF berhasil digabungkan!',
        filename: filename,
        pageCount: pageCount,
        layout: `${rows}x${cols}`,
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