# Form structure

All forms must use Zod schemas.
Structure the form schema into the directory src\components\forms.
Set the type of the forms in the filesrc\types\forms.ts
Forms must always use Zod validation.

## Example good form schema

```ts
import { z } from "zod";
import { GENDERS, EXPERIENCE_LEVELS } from "@/constants/const";

export const fitnessInfoSchema = z.object({
  height: z.coerce
    .number()
    .positive("Height must be a positive number.")
    .max(300, "Height must not exceed 300 cm.")
    .multipleOf(0.01, "Maximum 2 decimal places allowed."),

  weight: z.coerce
    .number()
    .positive("Weight must be a positive number.")
    .max(500, "Weight must not exceed 500 kg.")
    .multipleOf(0.01, "Maximum 2 decimal places allowed."),

  age: z.coerce
    .number()
    .int("Age must be a whole number.")
    .positive("Age must be a positive number.")
    .max(120, "Age must not exceed 120."),

  gender: z.enum(GENDERS, {
    message: "Please select a valid gender.",
  }),

  experience_level: z.enum(EXPERIENCE_LEVELS, {
    message: "Please select a valid experience level.",
  }),
});

export type FitnessInfoFormData = z.infer<typeof fitnessInfoSchema>;
export type FitnessInfoFormErrors = Partial<
  Record<keyof FitnessInfoFormData, string>
>;
```

# API to backend

Always check the file backend/routes/api.php for the routes
All API responses must be typed.
All API calls must go through a central API service layer.
Never hardcode backend routes. Always reference the backend route structure.

# UI development

Always prefer shadcn/ui components over building custom UI components from scratch.
Follow the composition pattern used by shadcn (Card, CardHeader, CardContent, etc.).
Keep UI components presentational and move logic to hooks or parent components when possible.
Use Tailwind utilities instead of custom CSS whenever possible.
Maintain consistent spacing, typography, and layout using Tailwind design tokens.
When a component is needed, install it using the shadcn CLI instead of manually copying code.

Example:

npx shadcn@latest add button
npx shadcn@latest add card
npx shadcn@latest add dialog
npx shadcn@latest add input
npx shadcn@latest add button card input dialog checkbox badge

Always import components from this folder.

Example:

import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"

Icons must come from lucide-react, which is the default icon library for shadcn.

Example:

import { Brain, Sparkles } from "lucide-react"

Toast Notifications

Use the installed toast system.

Example:

```ts
toast.success("Profile updated.", {
  position: "top-center",
  duration: 5000,
  style: {
    background: "#22C55E",
    color: "#fff",
  },
});

toast.error(error.message, {
  position: "top-center",
  duration: 5000,
  style: { background: "#FF4D4F", color: "#fff" },
});
```

# Project Overview

This is a React TypeScript frontend application built with Vite, featuring a fitness/workout planning system. The application includes user authentication, dashboard functionality, and workout plan generation. It uses a Laravel backend API and follows modern React patterns with shadcn/ui components.

## Technology Stack

- **Frontend Framework**: React 19 with TypeScript
- **Build Tool**: Vite
- **Styling**: Tailwind CSS with shadcn/ui components
- **Routing**: React Router DOM v7
- **State Management**: React Context API
- **HTTP Client**: Axios with interceptors
- **Form Validation**: Zod schemas
- **Icons**: Lucide React
- **Notifications**: Sonner toast system

# Project Structure

## Directory Organization

- `src/components/`: Reusable UI components
  - `ui/`: shadcn/ui components (button, card, input, etc.)
  - `forms/`: Form schemas and validation logic
  - `dashboard/`: Dashboard-specific components
  - `welcome/`: Landing page components
- `src/pages/`: Page components (one per route)
- `src/layouts/`: Layout components (AuthLayout, DashboardLayout, ProtectedRoute)
- `src/contexts/`: React contexts for global state
- `src/hooks/`: Custom React hooks
- `src/services/`: API service functions
- `src/types/`: TypeScript type definitions
- `src/lib/`: Utility libraries and configurations
- `src/constants/`: Application constants and enums
- `src/routes/`: Router configuration
- `src/styles/`: Global styles and CSS

## File Naming Conventions

- Components: PascalCase (e.g., `UserProfile.tsx`)
- Files: camelCase for utilities, PascalCase for components
- Directories: camelCase
- Type files: Same name as the feature (e.g., `auth.ts` for auth types)
- Form schemas: `{feature}Schema.ts` in `src/components/forms/`

# Routing and Navigation

## Router Structure

- Uses `createBrowserRouter` from React Router DOM
- Nested routing with layout components
- Authentication routes wrapped in `AuthLayout`
- Protected routes wrapped in `ProtectedRoute`
- Dashboard routes wrapped in `SideBar` layout

## Route Organization

- Public routes: `/` (welcome), `/register`, `/login`, etc.
- Protected routes: `/dashboard/*` with sidebar navigation
- Always use the router configuration in `src/routes/router.tsx`

# State Management

## Authentication Context

- `AuthContext` manages user authentication state
- Stores user data, access tokens, and loading states
- Handles session restoration on app startup
- Uses `tokenStore` for secure token management

## Context Usage

- Wrap the app with `AuthProvider` in `main.tsx`
- Access auth state with `useContext(AuthContext)`
- Never store sensitive data in localStorage directly

# Authentication and Authorization

## Authentication Flow

- JWT-based authentication with HttpOnly refresh tokens
- Access tokens stored in memory, refresh tokens in cookies
- Automatic token refresh with axios interceptors
- Session restoration on app reload

## Protected Routes

- Use `ProtectedRoute` component for authenticated pages
- Redirects to login if not authenticated
- Shows loading state during authentication check

# API Integration

## API Service Layer

- All API calls go through `src/lib/api.ts`
- Axios instance with base URL and interceptors
- Automatic token attachment to requests
- Token refresh handling with request queuing

## Service Organization

- API functions organized in `src/services/`
- One service file per feature (authentication.ts, user.ts)
- Typed request/response interfaces
- Error handling through `handleApiError.ts`

## Backend Routes

- Always check `backend/routes/api.php` for available routes
- Never hardcode API endpoints
- Use environment variables for base URLs

# Component Architecture

## Component Patterns

- Functional components with TypeScript
- Custom hooks for logic separation
- Presentational components in `ui/` directory
- Feature-specific components in respective directories

## shadcn/ui Integration

- Always prefer shadcn components over custom implementations
- Use composition pattern (Card, CardHeader, CardContent)
- Install new components with `npx shadcn@latest add {component}`
- Import from `@/components/ui/{component}`

## Component Props

- Use TypeScript interfaces for component props
- Prefer composition over extensive prop drilling
- Use React.forwardRef for components that need refs

# Type Definitions

## Type Organization

- Form types in `src/types/forms.ts` (inferred from Zod schemas)
- Feature types in respective files (auth.ts, user.ts, workout.ts)
- API response types defined in service files
- Use `z.infer<>` for form data types

## Type Naming

- Form data: `{Feature}FormData`
- Form errors: `{Feature}FormErrors`
- API responses: `{Feature}Response`
- Context values: `{Feature}ContextValue`

# Constants and Configuration

## Constants Structure

- Application constants in `src/constants/const.ts`
- Use `as const` for type-safe enums
- Group related constants together
- Export as named exports

## Environment Variables

- Use `import.meta.env.VITE_*` for client-side env vars
- Backend URL: `VITE_BACKEND_BASE_URL`
- Never expose sensitive data to client

# Styling and Theming

## Tailwind CSS

- Primary styling system with Tailwind utilities
- Use design tokens for consistent spacing and colors
- Avoid custom CSS unless necessary
- Use `tailwind-merge` and `clsx` for conditional classes
- Make everything always responsive for phone, tablet ...

## Theme Support

- Uses `next-themes` for dark/light mode support
- Theme provider wraps the application
- Access theme with `useTheme()` hook

## Component Styling

- shadcn components include Tailwind classes
- Custom components use Tailwind utilities
- Responsive design with Tailwind breakpoints

# Development Workflow

## Code Quality

- ESLint for code linting
- TypeScript for type checking
- Pre-commit hooks for quality checks
- Use `npm run lint` for linting

## Build Process

- `npm run dev`: Development server
- `npm run build`: Production build
- `npm run preview`: Preview production build
- TypeScript compilation included in build

## Import Patterns

- Use `@/` alias for `src/` directory
- Absolute imports for better tree-shaking
- Group imports: React, third-party, local components, utilities
- Avoid relative imports with `../`

## Error Handling

- API errors handled in `handleApiError.ts`
- User-friendly error messages with toast notifications
- Form validation errors from Zod schemas
- Graceful fallbacks for missing data

# Best Practices

## Performance

- Use React.memo for expensive components
- Lazy load routes and heavy components
- Optimize images and assets
- Minimize bundle size with tree-shaking

## Security

- Never store sensitive data in localStorage
- Use HttpOnly cookies for refresh tokens
- Validate all user inputs with Zod
- Sanitize data before rendering

## Accessibility

- Use semantic HTML elements
- Proper ARIA labels for screen readers
- Keyboard navigation support
- Color contrast compliance

## Testing

- Unit tests for utilities and hooks
- Integration tests for components
- E2E tests for critical user flows
- Test API error scenarios
