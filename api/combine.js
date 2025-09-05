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

    const { pagesPerSheet = 4, pageArrangement = 'side_by_side', paperSize = 'A4' } = req.body;

    try {
      console.log('Starting PDF processing...');
      console.log('Pages per sheet:', pagesPerSheet);
      console.log('Page arrangement:', pageArrangement);
      console.log('Paper size:', paperSize);
      
      // Load the PDF
      const pdfDoc = await PDFDocument.load(req.file.buffer);
      const pageCount = pdfDoc.getPageCount();

      console.log('PDF loaded, page count:', pageCount);

      if (pageCount === 0) {
        throw new Error('Invalid PDF file or empty PDF');
      }

      // Create new PDF document
      const newPdfDoc = await PDFDocument.create();

      // Define paper sizes
      const paperSizes = {
        'A4': { width: 595, height: 842 },
        'A3': { width: 842, height: 1191 },
        'A5': { width: 420, height: 595 },
        'Letter': { width: 612, height: 792 },
        'Legal': { width: 612, height: 1008 },
        'B4': { width: 708, height: 1001 },
        'B5': { width: 499, height: 708 }
      };

      const paper = paperSizes[paperSize] || paperSizes['A4'];
      console.log('Paper size:', paper);

      // Calculate layout dimensions
      const pagesPerSheetNum = parseInt(pagesPerSheet);
      let cols, rows;
      
      switch (pagesPerSheetNum) {
        case 2:
          cols = 2; rows = 1;
          break;
        case 4:
          cols = 2; rows = 2;
          break;
        case 5:
          cols = 3; rows = 2;
          break;
        case 6:
          cols = 3; rows = 2;
          break;
        case 8:
          cols = 4; rows = 2;
          break;
        default:
          cols = 2; rows = 2;
      }

      console.log('Layout:', cols + 'x' + rows);

      // Calculate page dimensions for each cell
      const cellWidth = paper.width / cols;
      const cellHeight = paper.height / rows;
      const margin = 10; // Small margin
      const pageWidth = cellWidth - (margin * 2);
      const pageHeight = cellHeight - (margin * 2);

      console.log('Cell dimensions:', cellWidth + 'x' + cellHeight);
      console.log('Page dimensions:', pageWidth + 'x' + pageHeight);

      // Process pages in batches
      for (let batchStart = 0; batchStart < pageCount; batchStart += pagesPerSheetNum) {
        console.log(`Processing batch starting at page ${batchStart + 1}`);
        
        // Create a new page for this batch
        const newPage = newPdfDoc.addPage([paper.width, paper.height]);
        
        // Process pages in this batch
        for (let i = 0; i < pagesPerSheetNum && (batchStart + i) < pageCount; i++) {
          const pageIndex = batchStart + i;
          console.log(`Processing page ${pageIndex + 1} in batch`);
          
          try {
            // Copy the page
            const [copiedPage] = await newPdfDoc.copyPages(pdfDoc, [pageIndex]);
            
            // Calculate position based on arrangement
            let x, y;
            
            if (pageArrangement === 'side_by_side') {
              // Horizontal arrangement
              const col = i % cols;
              const row = Math.floor(i / cols);
              x = col * cellWidth + margin;
              y = paper.height - ((row + 1) * cellHeight) + margin;
            } else {
              // Vertical arrangement
              const col = Math.floor(i / rows);
              const row = i % rows;
              x = col * cellWidth + margin;
              y = paper.height - ((row + 1) * cellHeight) + margin;
            }
            
            console.log(`Position for page ${pageIndex + 1}: (${x}, ${y})`);
            
            // Get original page size
            const originalSize = copiedPage.getSize();
            console.log(`Original page ${pageIndex + 1} size:`, originalSize);
            
            // Calculate scale to fit in cell
            const scaleX = pageWidth / originalSize.width;
            const scaleY = pageHeight / originalSize.height;
            const scale = Math.min(scaleX, scaleY);
            
            console.log(`Scale for page ${pageIndex + 1}:`, scale);
            
            // Draw the page
            newPage.drawPage(copiedPage, {
              x: x,
              y: y,
              xScale: scale,
              yScale: scale,
            });
            
            console.log(`Successfully drew page ${pageIndex + 1}`);
            
          } catch (pageError) {
            console.error(`Error processing page ${pageIndex + 1}:`, pageError);
            // Continue with other pages
          }
        }
        
        console.log(`Completed batch starting at page ${batchStart + 1}`);
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
        layout: `${cols}x${rows} (${pagesPerSheet} pages per sheet)`,
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