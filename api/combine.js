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
      if (pageArrangement === 'top_bottom') {
        return { rows: 2, cols: 1 };
      } else {
        return { rows: 1, cols: 2 };
      }
    case 4:
      if (pageArrangement === 'top_bottom') {
        return { rows: 4, cols: 1 };
      } else {
        return { rows: 2, cols: 2 };
      }
    case 5:
      if (pageArrangement === 'top_bottom') {
        return { rows: 5, cols: 1 };
      } else {
        return { rows: 2, cols: 3 };
      }
    case 6:
      if (pageArrangement === 'top_bottom') {
        return { rows: 6, cols: 1 };
      } else {
        return { rows: 2, cols: 3 };
      }
    case 8:
      if (pageArrangement === 'top_bottom') {
        return { rows: 8, cols: 1 };
      } else {
        return { rows: 2, cols: 4 };
      }
    default:
      if (pageArrangement === 'top_bottom') {
        return { rows: 2, cols: 1 };
      } else {
        return { rows: 2, cols: 2 };
      }
  }
}

export default async function handler(req, res) {
  // Apply CORS middleware
  corsMiddleware(req, res, () => {});

  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  try {
    // Handle file upload
    upload.single('pdfFile')(req, res, async (err) => {
      if (err) {
        return res.status(400).json({ 
          success: false, 
          error: err.message 
        });
      }

      if (!req.file) {
        return res.status(400).json({ 
          success: false, 
          error: 'No file uploaded' 
        });
      }

      const { pagesPerSheet = 4, pageArrangement = 'side_by_side', paperSize = 'A4' } = req.body;

      try {
        // Load the PDF
        const pdfDoc = await PDFDocument.load(req.file.buffer);
        const pages = pdfDoc.getPages();
        const pageCount = pages.length;

        if (pageCount === 0) {
          throw new Error('Invalid PDF file or empty PDF');
        }

        // Calculate layout
        const layout = calculateLayout(parseInt(pagesPerSheet), pageArrangement);
        const { rows, cols } = layout;

        // Get paper dimensions
        const paperDimensions = PAPER_SIZES[paperSize] || PAPER_SIZES.A4;
        const { width: pageWidth, height: pageHeight } = paperDimensions;

        // Calculate individual page size
        const cellWidth = pageWidth / cols;
        const cellHeight = pageHeight / rows;

        // Create new PDF document
        const newPdfDoc = await PDFDocument.create();

        // Process pages
        let currentPage = 0;

        while (currentPage < pageCount) {
          // Add new page
          const newPage = newPdfDoc.addPage([pageWidth, pageHeight]);

          // Place pages on current sheet
          for (let row = 0; row < rows && currentPage < pageCount; row++) {
            for (let col = 0; col < cols && currentPage < pageCount; col++) {
              const sourcePage = pages[currentPage];
              
              // Calculate position
              const x = col * cellWidth;
              const y = pageHeight - (row + 1) * cellHeight;

              // Get original page size
              const { width: originalWidth, height: originalHeight } = sourcePage.getSize();

              // Calculate scale to fit
              const scaleX = cellWidth / originalWidth;
              const scaleY = cellHeight / originalHeight;
              const scale = Math.min(scaleX, scaleY);

              // Calculate centered position
              const scaledWidth = originalWidth * scale;
              const scaledHeight = originalHeight * scale;
              const centeredX = x + (cellWidth - scaledWidth) / 2;
              const centeredY = y + (cellHeight - scaledHeight) / 2;

              // Copy page to new position
              newPage.drawPage(sourcePage, {
                x: centeredX,
                y: centeredY,
                xScale: scale,
                yScale: scale,
              });

              currentPage++;
            }
          }
        }

        // Generate PDF bytes
        const pdfBytes = await newPdfDoc.save();

        // Generate unique filename
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `combined_${timestamp}_${Math.random().toString(36).substr(2, 9)}.pdf`;

        // Return success response
        res.status(200).json({
          success: true,
          message: 'PDF berhasil digabungkan!',
          filename: filename,
          pageCount: pageCount,
          layout: `${rows}x${cols}`,
          downloadUrl: `/api/download?file=${encodeURIComponent(filename)}`,
          pdfData: pdfBytes.toString('base64')
        });

      } catch (error) {
        console.error('PDF processing error:', error);
        res.status(400).json({
          success: false,
          error: error.message
        });
      }
    });

  } catch (error) {
    console.error('Handler error:', error);
    res.status(500).json({
      success: false,
      error: 'Internal server error'
    });
  }
}
