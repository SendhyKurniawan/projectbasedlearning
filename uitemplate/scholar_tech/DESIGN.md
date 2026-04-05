# Design System Specification: The Architectural Scholar

## 1. Overview & Creative North Star
The "Architectural Scholar" is the Creative North Star for this design system. It moves beyond the generic "e-learning dashboard" to create a space that feels like a high-end digital atelier. We are blending the rigorous structure of academic excellence with the fluid, high-tech pulse of modern collaboration (LiveKit).

To achieve a signature feel, we reject "boxed-in" layouts. Instead, we use **Intentional Asymmetry** and **Tonal Depth**. This design system prioritizes breathing room, allowing complex Project-Based Learning (PBL) data to exist without clutter. We treat the UI as a series of sophisticated, layered surfaces that guide the eye through hierarchy rather than through rigid containers.

---

## 2. Color Philosophy & Logic
Our palette is rooted in a deep, intellectual blue (`primary`) and a vibrant, growth-oriented emerald (`secondary`). 

### The "No-Line" Rule
**Explicit Instruction:** Do not use 1px solid borders to section content. Traditional borders create visual noise that traps the user’s eye. Instead:
- **Define Boundaries** through background shifts (e.g., a `surface-container-low` section sitting on a `surface` background).
- **Use Negative Space** to imply structure.

### Surface Hierarchy & Nesting
Treat the UI as physical layers of "Frosted Glass" or "Fine Vellum." Use the `surface-container` tokens to create a natural "elevation" without relying on heavy shadows:
- **Base Layer:** `surface` (#faf8ff) for the main application background.
- **Structural Layer:** `surface-container-low` (#f3f3fe) for large sidebar regions or secondary panels.
- **Actionable Layer:** `surface-container-lowest` (#ffffff) for primary content cards or workspace areas to make them "pop" against the background.
- **Top-Level Layer:** `surface-bright` (#faf8ff) for floating navigation or utility bars.

### The "Glass & Gradient" Rule
To reflect the "Technologically Advanced" nature of the platform:
- **Glassmorphism:** For floating modals, live-chat overlays, or video controls, use semi-transparent `surface` colors with a `backdrop-filter: blur(12px)`.
- **Signature Textures:** Use subtle linear gradients transitioning from `primary` (#004ac6) to `primary_container` (#2563eb) for Hero sections and primary CTAs. This adds a "soul" to the UI that flat hex codes cannot provide.

---

## 3. Typography Scale
We utilize a dual-font strategy to balance academic authority with functional clarity.

*   **Display & Headlines (Manrope):** A geometric sans-serif that feels modern and architectural. Used for project titles and high-level statistics.
    *   `display-lg`: 3.5rem (The "Hero" statement)
    *   `headline-md`: 1.75rem (Standard page headers)
*   **Body & UI (Inter):** A workhorse for readability in complex PBL documentation.
    *   `title-md`: 1.125rem (Card titles, navigation items)
    *   `body-md`: 0.875rem (General interface text)
    *   `label-sm`: 0.6875rem (Metadata, micro-copy)

**Editorial Note:** Use `on_surface_variant` (#434655) for secondary body text to reduce visual weight and improve the "High-End" feel of the document-heavy sections.

---

## 4. Elevation & Depth
Depth in this design system is achieved through **Tonal Layering** rather than traditional drop shadows.

*   **The Layering Principle:** Stack `surface-container-lowest` cards on top of `surface-container` sections. This creates a soft, natural lift.
*   **Ambient Shadows:** For floating elements (Modals, Dropdowns), use extra-diffused shadows: `box-shadow: 0 12px 32px rgba(25, 27, 35, 0.06)`. The shadow color should be a tinted version of `on_surface` to mimic natural light.
*   **The "Ghost Border" Fallback:** If a border is required for accessibility, use the `outline_variant` (#c3c6d7) at **10% to 20% opacity**. 100% opaque borders are strictly forbidden.
*   **Interaction States:** When hovering over a card, do not just darken it; increase its elevation from `surface-container-lowest` to a `surface_bright` with a 4% ambient shadow.

---

## 5. Component Strategies

### Buttons
- **Primary:** Gradient fill (`primary` to `primary_container`) with white text. Roundedness: `md` (0.75rem).
- **Secondary:** Surface-based. Use `secondary_container` (#6cf8bb) with `on_secondary_container` (#00714d) text.
- **Tertiary:** No background; use `primary` text. Use for low-priority actions to maintain whitespace.

### Cards & Lists
- **Rule:** Forbid the use of divider lines.
- **Implementation:** Separate project list items using vertical spacing (1.5rem) or subtle background shifts between `surface-container-low` and `surface-container-lowest`. 
- **PBL Context:** Cards should feature a "Progress Accent"—a 4px vertical bar on the left edge using `secondary` (#006c49) to indicate active projects.

### Input Fields
- **Styling:** Use `surface_container_highest` (#e1e2ed) for the background with no border. Upon focus, animate a 2px bottom-border of `primary`.
- **States:** Error states should utilize the `error` token (#ba1a1a) for the label and a soft `error_container` tint for the field background.

### Collaborative Components (LiveKit Integration)
- **Video Tiles:** Use `xl` (1.5rem) rounded corners. Active speakers are highlighted with a `secondary_fixed` (#6ffbbe) outer "glow" rather than a hard stroke.
- **Presence Indicators:** Small, circular pips using `secondary` for "Active" and `surface_variant` for "Offline."

---

## 6. Do’s and Don’ts

### Do:
*   **Use Asymmetry:** Place project metadata off-center to create a modern, editorial rhythm.
*   **Embrace Whitespace:** If a section feels crowded, remove a container rather than adding a divider.
*   **Micro-interactions:** Use subtle "y-axis" shifts (2-4px) when hovering over interactive elements to reinforce the "layering" concept.

### Don’t:
*   **No "Box-Shadow Soup":** Avoid applying shadows to every element. Only floating elements get shadows; others use Tonal Layering.
*   **No High-Contrast Borders:** Never use #000 or high-opacity outlines. It breaks the "Academic Softness."
*   **No Pure Black:** For Dark Mode, always use `surface_dim` or `inverse_surface` to maintain the sophisticated, midnight-blue tone rather than "dead" black.

---

## 7. Accessibility Note
While we prioritize a premium aesthetic, all text-to-background combinations must meet **WCAG AA standards**. Ensure that `on_surface_variant` on `surface` maintain a minimum 4.5:1 contrast ratio. When using Glassmorphism, ensure the `backdrop-filter` is paired with a fallback background color for browsers that do not support blurs.