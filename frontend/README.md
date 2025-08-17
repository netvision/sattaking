# SattaKing Frontend

Vue 3 + Vite + Pinia powered public & admin interface for the SattaKing platform.

## Tech Stack
- Vue 3 (Composition API)
- Vite build tool
- Pinia state management
- Axios for API
- Tailwind utility classes (via existing custom styles)

## Environment Variables
Create a `frontend/.env` file:
```
VITE_API_BASE_URL=https://api.sattaking.app
```
Adjust for local dev if needed.

## Install & Run
```
cd frontend
npm install
npm run dev
```
Dev server usually runs at http://localhost:3000 or shown port.

## Build
```
npm run build
```
Output goes to `dist/` (ignored by git).

## Key Directories
- `src/views` – pages (Home, Results, Archive, Admin, SlotHistory)
- `src/components` – reusable UI (SlotCard, etc.)
- `src/stores` – Pinia stores (slots, results, auth)
- `src/services/api.js` – Axios instance & endpoints

## Adding Slot History
Route: `/results/slot/:id`
Fetches slot details + historical results via `/results?slot_id=<id>`.

## Deployment (Static)
1. Run `npm run build`.
2. Serve `frontend/dist` via nginx/Apache or copy into backend public path if desired.
3. Ensure API base URL points to production.

## Suggested Git Commit Flow
```
git add frontend src controllers models .gitignore
git commit -m "feat(frontend): initial public + admin UI"
```

## License
Internal project; add license if required.
