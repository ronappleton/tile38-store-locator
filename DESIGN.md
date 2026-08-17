# DESIGN — tile38-store-locator

The map is the proof: a restrained spatial instrument that makes one million
indexed stores feel tangible without turning the page into a dashboard of cards.

## Direction

- **Mode:** Experience. The live map and measured query answer lead.
- **World:** survey instrument over dark map paper; teal for active index state,
  amber for returned stores and measured attention.
- **Tone:** precise, technical, calm, and honest about synthetic data.
- **Signature:** a small latency HUD pinned to the map and a narrow evidence
  rail showing the exact PHP call.

## Color

| Role        | Value     | Use                             |
| ----------- | --------- | ------------------------------- |
| Ground      | `#07110f` | map and page ground             |
| Panel       | `#0b1916` | evidence rail                   |
| Text        | `#d6e0dc` | primary copy                    |
| Muted       | `#78928a` | labels and metadata             |
| Teal        | `#2dd4bf` | active state and nearest result |
| Bright teal | `#99f6e4` | hover and emphasis              |
| Amber       | `#fbbf24` | returned stores and benchmark   |

## Typography

- Familjen Grotesk for display numbers and the main promise.
- IBM Plex Sans for explanatory copy and controls.
- JetBrains Mono for commands, counts, timings, and version labels.

## Composition

1. A compact header identifies the client and Tile38 versions.
2. The map owns the largest surface and renders only bounded query results.
3. The evidence rail states the million-store premise, live count, latency,
   query code, and benchmark action.
4. Mobile stacks the map above the evidence rail without hiding the timing.

## Interaction

- Map click runs `NEARBY` with `LIMIT 12` and no radius.
- Map movement runs `WITHIN` with the current viewport bounds.
- Markers are intentionally sparse: the result set is the visual proof.
- Every query reports elapsed milliseconds from the Laravel service boundary.

## Accessibility

- Contrast-verified dark tokens.
- Keyboard-accessible benchmark button and map fallback text.
- Result count and timing are visible as text, not only map markers.
- Synthetic-data status is stated in the page copy and README.
