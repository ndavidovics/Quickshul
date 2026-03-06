<style>
.heb-key {
    background: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 5px 0;
    width: 30px;
    text-align: center;
    cursor: pointer;
    font-family: serif;
    font-size: 1.05rem;
    color: #1a2d5a;
    transition: background 0.1s;
    line-height: 1.4;
}
.heb-key:hover  { background: #e8e8e8; }
.heb-key:active { background: #d0d0d0; }
</style>

<div id="heb-kb" role="dialog" aria-label="Hebrew keyboard"
     style="display:none;position:fixed;z-index:9999;background:#fff;border:1px solid #ddd;
            border-radius:12px;padding:0.75rem;box-shadow:0 8px 32px rgba(0,0,0,0.18);
            width:316px;user-select:none;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.6rem">
        <span style="font-size:0.75rem;font-weight:600;color:#888;letter-spacing:0.05em;text-transform:uppercase">Hebrew Keyboard</span>
        <button type="button" id="heb-kb-close"
                style="background:none;border:none;cursor:pointer;font-size:1.1rem;color:#999;line-height:1;padding:2px 4px">✕</button>
    </div>
    <div id="heb-kb-keys" style="display:flex;flex-wrap:wrap;gap:4px;direction:rtl"></div>
    <div style="display:flex;gap:4px;margin-top:6px">
        <button type="button" class="heb-key" data-char=" "
                style="flex:2;width:auto;font-size:0.72rem;color:#666;font-family:inherit">Space</button>
        <button type="button" class="heb-key" data-char="__bs__"
                style="flex:2;width:auto;font-size:0.85rem">⌫</button>
    </div>
</div>

<script>
(function () {
    var LETTERS = ['א','ב','ג','ד','ה','ו','ז','ח','ט','י',
                   'כ','ך','ל','מ','ם','נ','ן','ס','ע','פ',
                   'ף','צ','ץ','ק','ר','ש','ת'];

    var kb          = document.getElementById('heb-kb');
    var keysDiv     = document.getElementById('heb-kb-keys');
    var activeInput = null;

    LETTERS.forEach(function (ch) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'heb-key';
        btn.dataset.char = ch;
        btn.textContent = ch;
        keysDiv.appendChild(btn);
    });

    kb.addEventListener('mousedown', function (e) {
        e.preventDefault();
        var btn = e.target.closest('.heb-key');
        if (!btn) return;
        var ch = btn.dataset.char;
        if (ch === '__bs__') deleteChar();
        else insertChar(ch);
    });

    document.getElementById('heb-kb-close').addEventListener('click', hideKb);

    document.addEventListener('mousedown', function (e) {
        if (kb.style.display === 'none') return;
        if (!kb.contains(e.target) && !e.target.closest('[data-heb-trigger]')) {
            hideKb();
        }
    });

    function showKb(input) { activeInput = input; positionNear(input); kb.style.display = 'block'; }
    function hideKb()      { kb.style.display = 'none'; }

    function positionNear(input) {
        var rect    = input.getBoundingClientRect();
        var kbW     = 316;
        var left    = Math.max(6, Math.min(rect.left, window.innerWidth - kbW - 10));
        var topBelow = rect.bottom + 6;
        var topAbove = rect.top - 215;
        var top     = (topBelow + 215 > window.innerHeight && topAbove > 6) ? topAbove : topBelow;
        kb.style.left = left + 'px';
        kb.style.top  = top  + 'px';
    }

    function insertChar(ch) {
        if (!activeInput) return;
        var s = activeInput.selectionStart, e = activeInput.selectionEnd;
        activeInput.value = activeInput.value.slice(0, s) + ch + activeInput.value.slice(e);
        activeInput.setSelectionRange(s + 1, s + 1);
        activeInput.focus();
    }

    function deleteChar() {
        if (!activeInput) return;
        var s = activeInput.selectionStart, e = activeInput.selectionEnd;
        if (s !== e) {
            activeInput.value = activeInput.value.slice(0, s) + activeInput.value.slice(e);
            activeInput.setSelectionRange(s, s);
        } else if (s > 0) {
            activeInput.value = activeInput.value.slice(0, s - 1) + activeInput.value.slice(s);
            activeInput.setSelectionRange(s - 1, s - 1);
        }
        activeInput.focus();
    }

    document.querySelectorAll('.heb-input').forEach(function (input) {
        var wrap = document.createElement('div');
        wrap.style.cssText = 'display:flex;gap:6px;align-items:center';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        input.style.flex = '1';

        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.setAttribute('data-heb-trigger', '1');
        trigger.title = 'Open Hebrew keyboard';
        trigger.textContent = 'א';
        trigger.style.cssText =
            'padding:0 10px;height:38px;font-family:serif;font-size:1.2rem;' +
            'background:#f5f5f5;border:1px solid #ddd;border-radius:6px;cursor:pointer;' +
            'flex-shrink:0;color:#1a2d5a;transition:background 0.15s';
        trigger.addEventListener('mouseover', function () { this.style.background = '#e8e8e8'; });
        trigger.addEventListener('mouseout',  function () { this.style.background = '#f5f5f5'; });
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            if (kb.style.display === 'none' || activeInput !== input) showKb(input);
            else hideKb();
        });
        wrap.appendChild(trigger);
    });
})();
</script>
