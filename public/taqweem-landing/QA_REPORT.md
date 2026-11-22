# TAQWEEM v3 — UI/UX / Frontend QA report
Date: 2026-11-21

## Scope
- Source base: v2 refined zip recovered from previous conversation.
- No Laravel backend files were present or altered.
- Assets retained; exterior trim remains the completed clinic shot, not construction footage.

## Source checks / confirmed
- Single index page with six narrative sections in this order: cinema, approach, specialists, journey, care-process, visit.
- All section navigation and inline anchor hrefs refer to actual IDs.
- Main treatment video appears only once inside the unified `journey` chapter.
- JavaScript syntax checks: passed using `node --check`.
- Preview PHP router syntax: passed via `php -l`.
- Internal images, video and GLB asset references: verified to exist.
- Full video source metadata: 1280x720 assets (NOT 4K).
- WebGL2 failure path: responsive static fallback displays rather than blank canvas.
- Image/poster-based UI check using headless Chromium with HTML/CSS/JS embedded into an in-memory page (local HTTP navigation was denied by browser administrative policy):
  * At desktop 1440x900 and mobile 390x844, no horizontal document overflow.
  * Mobile menu open and close: passed.
  * Specialist role selection: updates detail copy.
  * Booking steps: update preview text; CTA reports no appointment created.
  * Reduced-motion view retains static reading of the scan chapter.
  * No uncaught page JavaScript exceptions in this inline preview.

## Limitations / gates still open
- Live HTTP/browser playback of Video Seeking and transitions, including reverse scroll, was NOT executed in the restricted browser environment. Test on Windows Chrome and iPhone/Safari before approval.
- Actual GPU WebGL2 output was NOT verified: the headless test browser returned `WebGL2 not supported`, so its fallback path was checked instead.
- The doorway edit intentionally contains a dark dip: the two film sets do not physically match. Do not market it as a single seamless 4K camera shot.
- Some old MedAssist environment signage may remain in interior footage; CSS reframing is a mitigation, not full asset cleanup.
- No live doctor directory, booking integration, login, AI diagnostic, or Laravel endpoint was tested or implied.
- No measured Lighthouse, real-device INP/LCP/FPS, or network load benchmarks were made.

## Acceptance checks on the user's computer
1. Open with VS Code PHP Server or RUN_PREVIEW_WINDOWS.bat.
2. Confirm first exterior frame shows a fully constructed clinic, and video-to-video dark dip at the entrance feels intentional.
3. Test forward AND reverse scroll, especially at the cinema threshold and 3D cleaning sweep.
4. Verify the stain-to-clean effect is actually rendered on the 3D mesh (not only its fallback still) with WebGL2 enabled.
5. Test mobile 390px and 320px, landscape, accessibility focus, keyboard controls, reduced motion, and Safari if available.
6. Check all footage for outdated branding/signage; replace shots if visible.
7. Only after signoff, coordinate Frontend-to-Laravel integration without editing backend routes or authentication logic.
