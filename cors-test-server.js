import http from 'http';
import fs from 'fs';
import path from 'path';
import {fileURLToPath} from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const server = http.createServer((req, res) => {
    const urlPath = req.url.split('?')[0];
    const filePath = path.join(__dirname, 'cors-test-pages', urlPath === '/' ? 'index.html' : urlPath);
    fs.readFile(filePath, (err, content) => {
        if (err) {
            res.writeHead(404);
            res.end('Not found');
            return;
        }
        res.writeHead(200, {'Content-Type': 'text/html'});
        res.end(content);
    });
});

server.listen(3000, '0.0.0.0', () => {
    console.log('CORS test server running on http://0.0.0.0:3000');
});
