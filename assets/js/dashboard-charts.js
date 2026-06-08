/**
 * dashboard-charts.js – Vanilla Canvas bar chart for dashboard analytics
 * No external libraries needed.
 */
const DashboardCharts = {

  /**
   * Draw an animated bar chart on a canvas element
   * @param {string} canvasId - Canvas element ID
   * @param {Array} data - Array of {label, value}
   * @param {object} opts - Optional config
   */
  barChart(canvasId, data, opts = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || !data.length) return;

    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.parentElement.getBoundingClientRect();

    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    canvas.style.width = rect.width + 'px';
    canvas.style.height = rect.height + 'px';
    ctx.scale(dpr, dpr);

    const w = rect.width;
    const h = rect.height;
    const padding = { top: 20, right: 20, bottom: opts.paddingBottom || 80, left: 45 };
    const chartW = w - padding.left - padding.right;
    const chartH = h - padding.top - padding.bottom;

    const maxVal = Math.max(...data.map(d => d.value), 1);
    const barCount = data.length;
    const gap = Math.min(16, chartW / barCount * 0.3);
    const barW = Math.max(12, (chartW - gap * (barCount + 1)) / barCount);

    // Colors
    const colors = opts.colors || [
      '#004aad', '#004099', '#003685', '#002d70',
      '#1a5cbd', '#336ecc', '#4d81db', '#6693eb',
      '#80a6fa', '#99b8ff'
    ];
    const gridColor = 'rgba(0,0,0,0.06)';
    const textColor = 'rgba(0,0,0,0.4)';
    const labelColor = 'rgba(0,0,0,0.6)';

    // Grid lines
    const gridSteps = 5;
    ctx.strokeStyle = gridColor;
    ctx.lineWidth = 1;
    ctx.font = '11px Inter, sans-serif';
    ctx.fillStyle = textColor;
    ctx.textAlign = 'right';

    for (let i = 0; i <= gridSteps; i++) {
      const y = padding.top + (chartH / gridSteps) * i;
      const val = Math.round(maxVal - (maxVal / gridSteps) * i);

      ctx.beginPath();
      ctx.moveTo(padding.left, y);
      ctx.lineTo(w - padding.right, y);
      ctx.stroke();

      ctx.fillText(val, padding.left - 8, y + 4);
    }

    // Helper: convert 3/6-digit hex to rgba string
    function hexToRgba(hex, alpha = 1) {
      const h = hex.replace('#', '');
      const bigint = parseInt(h.length === 3 ? h.split('').map(c => c + c).join('') : h, 16);
      const r = (bigint >> 16) & 255;
      const g = (bigint >> 8) & 255;
      const b = bigint & 255;
      return `rgba(${r},${g},${b},${alpha})`;
    }

    // Helper: draw wrapped label (max 2 lines). Draw from top; truncate second line with ellipsis if needed.
    function drawWrappedLabel(ctx, text, x, y, maxWidth, lineHeight = 14) {
      ctx.textBaseline = 'top';
      const words = String(text).split(' ');
      const lines = [];
      let current = '';

      for (let i = 0; i < words.length; i++) {
        const w = words[i];
        const test = current ? current + ' ' + w : w;
        if (ctx.measureText(test).width <= maxWidth) {
          current = test;
        } else {
          if (current) lines.push(current);
          current = w;
          if (lines.length === 1) {
            // Already have first line; put remaining words into second line then break
            let second = current;
            for (let j = i + 1; j < words.length; j++) {
              const tryLine = second + ' ' + words[j];
              if (ctx.measureText(tryLine).width <= maxWidth) {
                second = tryLine;
                i = j; // advance outer loop
              } else {
                break;
              }
            }
            // Truncate second line to fit and add ellipsis
            let truncated = second;
            while (ctx.measureText(truncated + '…').width > maxWidth && truncated.length > 0) {
              truncated = truncated.slice(0, -1);
            }
            lines.push(truncated + (truncated.length < second.length ? '…' : ''));
            current = '';
            break;
          }
        }
      }
      if (current && lines.length < 2) lines.push(current);

      // Ensure max 2 lines
      if (lines.length > 2) lines.length = 2;

      lines.forEach((ln, idx) => {
        ctx.fillText(ln, x, y + idx * lineHeight);
      });
    }

    // Animate bars
    let progress = 0;
    const animDuration = 800;
    const startTime = performance.now();

    function animate(now) {
      progress = Math.min(1, (now - startTime) / animDuration);
      const ease = 1 - Math.pow(1 - progress, 3); // easeOutCubic

      // Clear the entire chart area (preserving Y-axis labels on the left)
      ctx.clearRect(padding.left - 2, 0, w - padding.left + 2, h);

      // Redraw grid
      ctx.strokeStyle = gridColor;
      ctx.lineWidth = 1;
      for (let i = 0; i <= gridSteps; i++) {
        const y = padding.top + (chartH / gridSteps) * i;
        ctx.beginPath();
        ctx.moveTo(padding.left, y);
        ctx.lineTo(w - padding.right, y);
        ctx.stroke();
      }

      // Draw bars
      data.forEach((d, i) => {
        const x = padding.left + gap + i * (barW + gap);
        const barH = (d.value / maxVal) * chartH * ease;
        const y = padding.top + chartH - barH;

        // Bar gradient (use rgba for smoother alpha)
        const grad = ctx.createLinearGradient(x, y, x, padding.top + chartH);
        const color = colors[i % colors.length];
        grad.addColorStop(0, color);
        grad.addColorStop(1, hexToRgba(color, 0.12));

        // Bar with rounded top
        const radius = Math.min(6, barW / 2);
        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.moveTo(x, padding.top + chartH);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.lineTo(x + barW - radius, y);
        ctx.quadraticCurveTo(x + barW, y, x + barW, y + radius);
        ctx.lineTo(x + barW, padding.top + chartH);
        ctx.closePath();
        ctx.fill();

        // Glow effect (isolated)
        if (ease > 0.5) {
          ctx.save();
          ctx.shadowColor = hexToRgba(color, 0.28);
          ctx.shadowBlur = 10;
          ctx.fillStyle = grad;
          ctx.fill();
          ctx.restore();
        }

        // Value label on top
        if (ease > 0.8) {
          ctx.save();
          ctx.fillStyle = 'rgba(0,0,0,0.7)';
          ctx.font = 'bold 12px Inter, sans-serif';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'bottom';
          ctx.fillText(d.value, x + barW / 2, y - 6);
          ctx.restore();
        }
      });

      // Bottom labels (always drawn) - using manual 2-line wrap
      ctx.fillStyle = labelColor;
      ctx.font = '11px Inter, sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'top';
      
      data.forEach((d, i) => {
        const x = padding.left + gap + i * (barW + gap) + barW / 2;
        const labelY = h - padding.bottom + 12;
        const text = String(d.label).trim();
        const words = text.split(/\s+/);

        if (text.length > 10 && words.length >= 2) {
          const mid = Math.ceil(words.length / 2);
          if (words.length === 2) {
            ctx.fillText(words[0], x, labelY);
            ctx.fillText(words[1], x, labelY + 14);
          } else {
            ctx.fillText(words.slice(0, mid).join(' '), x, labelY);
            ctx.fillText(words.slice(mid).join(' '), x, labelY + 14);
          }
        } else {
          ctx.fillText(text, x, labelY);
        }
      });

      if (progress < 1) {
        requestAnimationFrame(animate);
      }
    }

    requestAnimationFrame(animate);

    // Resize handler
    if (!canvas._resizeHandler) {
      canvas._resizeHandler = () => {
        DashboardCharts.barChart(canvasId, data, opts);
      };
      window.addEventListener('resize', () => {
        clearTimeout(canvas._resizeTimer);
        canvas._resizeTimer = setTimeout(canvas._resizeHandler, 200);
      });
    }
  },

  /**
   * Draw a mini donut/ring chart
   */
  donutChart(canvasId, data, opts = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || !data.length) return;

    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const size = Math.min(canvas.parentElement.offsetWidth, canvas.parentElement.offsetHeight);

    canvas.width = size * dpr;
    canvas.height = size * dpr;
    canvas.style.width = size + 'px';
    canvas.style.height = size + 'px';
    ctx.scale(dpr, dpr);

    const cx = size / 2;
    const cy = size / 2;
    const radius = size / 2 - 10;
    const thickness = opts.thickness || 20;
    const total = data.reduce((s, d) => s + d.value, 0);

    const colors = opts.colors || [
      '#004aad', '#004099', '#003685', '#002d70',
      '#1a5cbd', '#336ecc', '#4d81db'
    ];

    let startAngle = -Math.PI / 2;

    // Background ring
    ctx.beginPath();
    ctx.arc(cx, cy, radius, 0, Math.PI * 2);
    ctx.strokeStyle = 'rgba(255,255,255,0.04)';
    ctx.lineWidth = thickness;
    ctx.stroke();

    // Segments
    data.forEach((d, i) => {
      const sliceAngle = (d.value / total) * Math.PI * 2;
      ctx.beginPath();
      ctx.arc(cx, cy, radius, startAngle, startAngle + sliceAngle);
      ctx.strokeStyle = colors[i % colors.length];
      ctx.lineWidth = thickness;
      ctx.lineCap = 'round';
      ctx.stroke();
      startAngle += sliceAngle;
    });

    // Center text
    if (opts.centerText) {
      ctx.fillStyle = '#fff';
      ctx.font = 'bold 22px Inter, sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText(opts.centerText, cx, cy - 6);

      ctx.fillStyle = 'rgba(255,255,255,0.4)';
      ctx.font = '11px Inter, sans-serif';
      ctx.fillText(opts.centerSub || '', cx, cy + 14);
    }
  }
};
