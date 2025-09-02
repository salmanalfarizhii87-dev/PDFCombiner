export default function handler(req, res) {
  // Set CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method !== 'GET') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  const { file } = req.query;

  if (!file) {
    return res.status(400).json({ error: 'File parameter is required' });
  }

  // For Vercel serverless functions, we'll return the PDF data directly
  // The frontend will handle the download using the base64 data from combine API
  res.status(200).json({
    message: 'Use the pdfData from combine API response for download'
  });
}
