---
name: SokratCRM
description: "Bilingual operational CRM interface aligned with Sokrat VoIP visual identity."
colors:
  primary-red: "#ef4444"
  primary-red-dark: "#dc2626"
  primary-red-hover: "#dc2626"
  selection-tint: "rgba(239, 68, 68, 0.12)"
  page: "#f4f6fa"
  surface: "#ffffff"
  surface-soft: "#f8fafc"
  ink: "#172033"
  muted: "#596579"
  border: "#e5e7eb"
  success: "#10b981"
  success-surface: "#e9f8ef"
  neutral-state-surface: "#eef1f5"
  dark-page: "#151922"
  dark-surface: "#202631"
  dark-surface-soft: "#272e3a"
  dark-ink: "#f3f5f8"
  dark-muted: "#aeb7c6"
  dark-border: "rgba(255, 255, 255, 0.08)"
  dark-success: "#10b981"
  dark-success-surface: "#173c2a"
typography:
  headline:
    fontFamily: "'Plus Jakarta Sans', 'Cairo', sans-serif"
    fontSize: "clamp(25px, 3vw, 32px)"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "-0.02em"
  title:
    fontFamily: "'Plus Jakarta Sans', 'Cairo', sans-serif"
    fontSize: "15px"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "normal"
  body:
    fontFamily: "'Plus Jakarta Sans', 'Cairo', sans-serif"
    fontSize: "14px"
    fontWeight: 400
    lineHeight: 1.7
    letterSpacing: "normal"
  label:
    fontFamily: "'Plus Jakarta Sans', 'Cairo', sans-serif"
    fontSize: "11px"
    fontWeight: 800
    lineHeight: 1.2
    letterSpacing: "normal"
  numeric:
    fontFamily: "'JetBrains Mono', 'Plus Jakarta Sans', monospace"
    fontSize: "13px"
    fontWeight: 500
    lineHeight: 1.4
    letterSpacing: "normal"
rounded:
  xs: "6px"
  sm: "8px"
  control: "11px"
  icon: "12px"
  alert: "14px"
  panel: "16px"
  pill: "999px"
spacing:
  compact: "6px"
  xs: "8px"
  sm: "10px"
  md: "12px"
  lg: "18px"
  xl: "22px"
  shell: "32px"
components:
  button-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    typography: "{typography.label}"
    rounded: "{rounded.control}"
    padding: "0 15px"
    height: "44px"
  search-input:
    backgroundColor: "{colors.surface-soft}"
    textColor: "{colors.ink}"
    typography: "{typography.body}"
    rounded: "{rounded.control}"
    padding: "0 42px"
    height: "44px"
  filter:
    backgroundColor: "transparent"
    textColor: "{colors.muted}"
    typography: "{typography.label}"
    rounded: "{rounded.sm}"
    padding: "0 13px"
    height: "44px"
  filter-active:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.primary-red}"
    typography: "{typography.label}"
    rounded: "{rounded.sm}"
    padding: "0 13px"
    height: "44px"
  panel:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.panel}"
    padding: "18px"
  status-online:
    backgroundColor: "{colors.success-surface}"
    textColor: "{colors.success}"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: "6px 9px"
  navigation-item:
    backgroundColor: "transparent"
    textColor: "{colors.muted}"
    rounded: "{rounded.icon}"
    padding: "8px 10px"
    height: "49px"
  navigation-item-active:
    backgroundColor: "{colors.primary-red}"
    textColor: "{colors.surface}"
    rounded: "{rounded.icon}"
    padding: "8px 10px"
    height: "49px"
---

# Design System: SokratCRM (Aligned with Sokrat VoIP)

## Overview

SokratCRM strictly aligns with the Sokrat VoIP visual identity across Arabic and English. The design system is defined by an ultra-clean, tech-focused flat aesthetic with **zero box-shadows**, relying entirely on high-precision 1px borders and surface contrast, Electric Crimson Red interaction accents, and a three-tier typography hierarchy.

**Key Characteristics:**

- **Electric Crimson Red (`#ef4444`)**: signature primary accent, with `#dc2626` for hover/pressed states and `rgba(239, 68, 68, 0.12)` for selection tints.
- **Three-Tier Typography Stack**:
  1. Latin UI & Headings: `'Plus Jakarta Sans', sans-serif`
  2. Arabic UI & Headings: `'Cairo', sans-serif`
  3. Numbers, Telemetry, Tables, Timestamps, Codes & IP Addresses: `'JetBrains Mono', monospace`
- **Border-Led Flat Architecture (Strict Zero-Shadow Rule)**: all box-shadows, text-shadows, and glow filters are eliminated (`box-shadow: none !important`). Crisp 1px borders (`#e5e7eb` light, `rgba(255, 255, 255, 0.08)` dark) define visual hierarchy.
- **Online / Success**: `#10b981` (Emerald) reserved strictly for live/positive states.
- **Direction-Aware Layouts**: logical CSS properties (`inline-start`, `inline-end`) ensuring identical hierarchy across RTL and LTR.

## Colors

The palette is role-based: Electric Crimson Red interaction accent, neutral page and data surfaces, dark text, crisp 1px borders, and Emerald green state channel.

### Primary Accent & Interaction

- **Primary Accent** (`primary-red`): `#ef4444` (Electric Crimson Red) - active navigation, focus borders, selected filters, action emphasis.
- **Accent Hover / Pressed** (`primary-red-dark` / `primary-red-hover`): `#dc2626`.
- **Selection / Active Tint** (`selection-tint`): `rgba(239, 68, 68, 0.12)`.

### Neutral Surfaces & Borders

- **Page** (`page`): default application canvas (`#f4f6fa`).
- **Surface** (`surface`): cards, controls, navigation, and containers (`#ffffff`).
- **Soft Surface** (`surface-soft`): table headers, search fields, row hover (`#f8fafc`).
- **Ink** (`ink`): headings and primary content (`#172033`).
- **Muted** (`muted`): supporting copy, labels, timestamps (`#596579`).
- **Border** (`border`): crisp 1px boundary separation (`#e5e7eb`).
- **Dark Borders & Surfaces** (`dark-*`): dark-page (`#151922`), dark-surface (`#202631`), dark-border (`rgba(255, 255, 255, 0.08)`).

### State Channel

- **Success / Online** (`success`): `#10b981` (Emerald) - online counts, active connections, live status indicators.
- **Success Surface** (`success-surface`): `#e9f8ef`.

**The State Color Rule.** Red communicates interaction or product emphasis; emerald green communicates current positive/live state. Do not exchange those meanings.

## Typography

Sokrat VoIP three-tier font stack:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
```

### Font Hierarchy:

1. **Latin UI & Headings**: `'Plus Jakarta Sans', sans-serif` (weights: 400, 500, 600, 700, 800)
2. **Arabic UI & Headings**: `'Cairo', sans-serif` (weights: 200, 300 Light, 400 Regular, 500 Medium, 600 SemiBold) - calibrated for slender geometric balance with JetBrains Mono.
3. **Data Fields, Tables & Telemetry**: `'JetBrains Mono', 'Plus Jakarta Sans', 'Cairo', monospace`

### Application Rules:

```css
body {
  font-family: 'Plus Jakarta Sans', 'Cairo', sans-serif !important;
  font-weight: 400;
}

.font-mono, td, th, input, select, textarea, pre, code, .font-numeric, .badge-mono {
  font-family: 'JetBrains Mono', 'Plus Jakarta Sans', 'Cairo', monospace !important;
}

/* Sidebar Scrollbar Rule: Always hidden across browsers */
.crm-side, #crmSidebar {
  overflow-y: auto !important;
  scrollbar-width: none !important;
  -ms-overflow-style: none !important;
}
.crm-side::-webkit-scrollbar, #crmSidebar::-webkit-scrollbar {
  display: none !important;
  width: 0 !important;
  height: 0 !important;
}
```

## Strict Zero-Shadow Rule (Border-Led Flat Architecture)

Sokrat VoIP strictly rejects pseudo-3D elevations and shadows in favor of crisp 1px borders and surface contrast.

```css
*, *::before, *::after {
  box-shadow: none !important;
  text-shadow: none !important;
}
```

- **Sidebar Links & Active Indicators**: no glow (`0 12px 27px #dc26372c` removed); active items use `#ef4444` background with crisp borders.
- **Topbar & Header Panels**: no drop-shadows (`box-shadow: none !important`), framed by 1px `#e5e7eb` (light) or `rgba(255, 255, 255, 0.08)` (dark).
- **Cards, Tables, Modals & Dropdowns**: border-led flat surfaces.

## Layout

The application shell is a horizontal flex layout on desktop. The shared sidebar is 288px wide and sticky at full height; its persisted collapsed state is 88px. The main operational area fills the remaining width with 32px inline padding on the Technical Support surface and no fixed maximum-width container.

Surface hierarchy is linear: page header, one full-width status or context strip, then the primary searchable data panel. Panels use 18–22px internal spacing, while compact controls use 6–15px gaps and padding. Logical properties (`inline-start`, `inline-end`, and `text-align: start`) keep the same composition in RTL and LTR.

## Components

### Buttons

- **Secondary / Refresh**: a 44px white bordered control using `button-secondary`; hover changes the border and text to `#ef4444`. Zero shadow.
- **Primary**: a crimson red-filled (`#ef4444`), white-text button; hover darkens to `#dc2626`.
- **Focus**: all keyboard-focusable controls receive a 2px solid `#ef4444` outline.

### Chips & Badges

- **Filters**: compact controls inside a soft-surface group. Active state switches to white surface with `#ef4444` text and crisp 1px border.
- **Status Badges**: online states use `#10b981` (Emerald) pill badges with leading dot.

### Inputs / Fields

- **Search**: 44px high, soft-surface fill, 1px border (`#e5e7eb`), control radius, monospace/data font for input contents.
- **Focus**: `#ef4444` border, zero shadow.

### Navigation

- **Desktop**: 288px sticky sidebar with 49px items, 13px item corners, 32px icon tiles, 5px row gaps.
- **Active**: `#ef4444` flat background, white content, 1px border, zero shadow.
- **Hover**: pale crimson tint (`rgba(239, 68, 68, 0.12)`) and `#ef4444` text.

## Do's and Don'ts

### Do:
- **Do** use Electric Crimson Red (`#ef4444`) for primary actions and active states.
- **Do** use Emerald (`#10b981`) strictly for positive/live states.
- **Do** enforce `Plus Jakarta Sans` for Latin, `Cairo` for Arabic, and `JetBrains Mono` for numbers, tables, inputs, codes, and timestamps.
- **Do** enforce the zero-shadow rule globally (`box-shadow: none !important`).
- **Do** separate components exclusively with crisp 1px borders and surface contrast.

### Don't:
- **Don't** add box-shadows, glow filters, or drop-shadows anywhere in the UI.
- **Don't** use legacy red shades (`#dc2637`, `#b81829`).
- **Don't** use Tajawal or Instrument Sans as the primary typography.
- **Don't** format numeric data, timestamps, or IP addresses in proportional sans-serif fonts.
