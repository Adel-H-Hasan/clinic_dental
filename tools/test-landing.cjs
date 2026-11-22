const Module=require('node:module');const fs=require('node:fs');const path=require('node:path');
for(const name of ['handoff_curve_test.js','portal_geometry_test.js']){const file=path.resolve('public/taqweem-landing/tests',name);const m=new Module(file,module);m.filename=file;m.paths=module.paths;m._compile(fs.readFileSync(file,'utf8'),file);}
