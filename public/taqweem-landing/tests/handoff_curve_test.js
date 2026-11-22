/* Checks only deterministic timing and continuity math, NOT browser/WebGL playback. */
const assert=require('node:assert/strict');
const clamp=x=>Math.max(0,Math.min(1,x));
const smooth=(a,b,p)=>{const t=clamp((p-a)/(b-a));return t*t*(3-2*t);};
const phase=(a,b,c,d,p)=>smooth(a,b,p)*(1-smooth(c,d,p));
const core=p=>phase(.462,.510,.55,.635,p)*(1-smooth(.493,.571,p));
const world=p=>smooth(.465,.608,p);
const scale=p=>.89*(.32+.68*smooth(.458,.690,p));
let last=-1;
for(let p=.45;p<=.70;p+=.005){let s=scale(p);assert(s>=last-1e-9);last=s;}
assert(world(.535)>.4 && core(.535)>.1,'Hand-off must overlap between layers at mid range');
assert(world(.59)>.9 && core(.59)<.03,'Live tooth should dominate before portal exits');
assert(world(.69)>.99 && scale(.69)>.88,'GLB should be ready at the end of the tunnel');
assert(smooth(.918,.970,.99)>.99,'Editorial bridge should not fade before stage end');
console.log('PASS smooth GLB scale, layer overlap, bridge timing; values reversible');
