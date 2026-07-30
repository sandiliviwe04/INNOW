import express from "express";
import path from "path";
import fs from "fs";
import { PHPRequestHandler, PHP, HTTPMethod } from "@php-wasm/universal";
import { loadNodeRuntime, useHostFilesystem } from "@php-wasm/node";

async function startServer() {
  const app = express();
  const PORT = 3000;
  const projectRoot = process.cwd();

  app.use(express.json());
  app.use(express.urlencoded({ extended: true }));

  // Static Assets serving from frontend/public/assets
  app.use("/assets", express.static(path.join(projectRoot, "frontend/public/assets")));

  // Initialize PHP WASM Instance
  console.log("Initializing INNOW PHP WASM Engine...");
  let handler: PHPRequestHandler | null = null;

  try {
    const asyncifyPath = path.join(projectRoot, "node_modules/@php-wasm/node-8-3/asyncify");
    process.chdir(asyncifyPath);

    const webRoot = path.join(projectRoot, "frontend/public");

    handler = new PHPRequestHandler({
      documentRoot: webRoot,
      phpFactory: async () => {
        const runtimeId = await loadNodeRuntime("8.3", {
          emscriptenOptions: { processId: 1 }
        });
        const php = new PHP(runtimeId);
        useHostFilesystem(php);
        return php;
      }
    });
    console.log("INNOW PHP WASM Engine Ready at documentRoot:", webRoot);
  } catch (e) {
    console.error("PHP Engine Warning:", e);
  }

  // Forward all dynamic HTTP routes to PHP Front Controller
  app.all("*", async (req, res) => {
    // If request is for static asset file
    if (req.path.startsWith("/assets/") || req.path.startsWith("/favicon")) {
      const staticFile = path.join(projectRoot, "frontend/public", req.path);
      if (fs.existsSync(staticFile)) {
        return res.sendFile(staticFile);
      }
    }

    if (!handler) {
      return res.status(500).send("PHP WASM Engine starting...");
    }

    try {
      // Map all incoming URL routes to /index.php so PHP Front Controller handles them
      const phpUrl = `http://localhost/index.php`;
      const headers: Record<string, string> = {
        ...(req.headers as Record<string, string>),
        "x-original-uri": req.originalUrl
      };

      const method = (req.method.toUpperCase() as HTTPMethod) || "GET";

      const phpResponse: any = await handler.request({
        url: phpUrl,
        method,
        headers,
        body: typeof req.body === "object" ? JSON.stringify(req.body) : req.body
      });

      const statusCode = phpResponse.status || phpResponse.statusCode || 200;
      res.status(statusCode);

      if (phpResponse.headers) {
        for (const [hk, hv] of Object.entries(phpResponse.headers)) {
          if (hk.toLowerCase() !== "transfer-encoding") {
            res.setHeader(hk, hv as string);
          }
        }
      }
      res.send(phpResponse.text);
    } catch (err: any) {
      console.error("PHP Router Error:", err);
      res.status(500).send(`PHP Execution Error: ${err?.message || err}`);
    }
  });

  app.listen(PORT, "0.0.0.0", () => {
    console.log(`INNOW Digital Attendance PHP Server running on http://0.0.0.0:${PORT}`);
  });
}

startServer();
