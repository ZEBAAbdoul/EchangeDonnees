{{-- resources/views/auth/partials/dgti-styles.blade.php --}}
<style>
    .dgti-auth {
        --c-primary: #0b2a5b;
        --c-primary-2: #1456b8;
        --c-accent: #f2b705;
        --c-text: #1f2937;
        --c-muted: #6b7280;
        --c-border: #d9dee7;
        --c-bg: #f3f6fb;
        --c-danger: #b42318;
        --c-success: #137a3f;
        --c-info: #1456b8;

        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 24px 16px;
        background: var(--c-bg);
        color: var(--c-text);
    }

    .dgti-card {
        display: grid;
        grid-template-columns: 1fr 1.1fr;
        width: 100%;
        max-width: 980px;
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(11, 42, 91, .15);
    }

    /* ---------- Panneau institutionnel ---------- */
    .dgti-aside {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 36px 32px;
        color: #fff;
        background: linear-gradient(160deg, var(--c-primary) 0%, var(--c-primary-2) 100%);
        overflow: hidden;
    }

    .dgti-aside::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 5px;
        background: var(--c-accent);
    }

    .dgti-tree {
        position: absolute;
        right: -30px;
        bottom: 40px;
        font-size: 220px;
        opacity: .07;
        pointer-events: none;
    }

    .dgti-aside-content {
        position: relative;
        z-index: 1;
    }

    .dgti-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #fff;
        text-decoration: none;
    }

    .dgti-logo {
        flex: 0 0 56px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #fff;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dgti-logo img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        border-radius: 50%;
    }

    .dgti-brand-text {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .dgti-brand-name {
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: .08em;
    }

    .dgti-brand-sub {
        font-size: .75rem;
        opacity: .85;
    }

    .dgti-aside-body {
        margin-top: 48px;
    }

    .dgti-kicker {
        margin: 0 0 8px;
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--c-accent);
    }

    .dgti-headline {
        margin: 0 0 24px;
        font-size: 1.45rem;
        line-height: 1.35;
        font-weight: 700;
    }

    .dgti-features {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 14px;
    }

    .dgti-features li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: .92rem;
    }

    .dgti-features i {
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .14);
        color: var(--c-accent);
    }

    .dgti-aside-footer {
        position: relative;
        z-index: 1;
        margin: 32px 0 0;
        font-size: .75rem;
        opacity: .8;
    }

    /* ---------- Formulaire ---------- */
    .dgti-main {
        padding: 40px 40px 32px;
    }

    .dgti-heading h1 {
        margin: 0 0 4px;
        font-size: 1.7rem;
        font-weight: 700;
        color: var(--c-primary);
    }

    .dgti-heading p {
        margin: 0 0 24px;
        color: var(--c-muted);
    }

    .dgti-alert {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 14px;
        margin-bottom: 18px;
        border-radius: 10px;
        font-size: .9rem;
        border: 1px solid transparent;
    }

    .dgti-alert--success { background: #e8f6ee; border-color: #b7e2c6; color: var(--c-success); }
    .dgti-alert--info    { background: #e8f0fc; border-color: #bcd2f5; color: var(--c-info); }
    .dgti-alert--danger  { background: #fdecea; border-color: #f5c2bd; color: var(--c-danger); }

    .dgti-group { margin-bottom: 18px; }

    .dgti-label {
        display: block;
        margin-bottom: 6px;
        font-size: .85rem;
        font-weight: 600;
    }

    .dgti-field { position: relative; }

    .dgti-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--c-muted);
        pointer-events: none;
    }

    .dgti-input {
        width: 100%;
        height: 46px;
        padding: 0 44px 0 40px;
        border: 1px solid var(--c-border);
        border-radius: 10px;
        font-size: .95rem;
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
    }

    .dgti-input:focus {
        outline: none;
        border-color: var(--c-primary-2);
        box-shadow: 0 0 0 3px rgba(20, 86, 184, .18);
    }

    .dgti-input.is-invalid { border-color: var(--c-danger); }

    .dgti-toggle {
        position: absolute;
        right: 6px;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border: 0;
        background: transparent;
        color: var(--c-muted);
        cursor: pointer;
        border-radius: 8px;
    }

    .dgti-toggle:hover { color: var(--c-primary); background: var(--c-bg); }

    .dgti-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
        font-size: .88rem;
    }

    .dgti-check { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; margin: 0; }
    .dgti-check input { accent-color: var(--c-primary-2); }

    .dgti-forgot { color: var(--c-primary-2); text-decoration: none; font-weight: 500; }
    .dgti-forgot:hover { text-decoration: underline; }

    .dgti-btn {
        width: 100%;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border: 0;
        border-radius: 10px;
        background: var(--c-primary);
        color: #fff;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, transform .05s;
    }

    .dgti-btn:hover { background: var(--c-primary-2); }
    .dgti-btn:active { transform: translateY(1px); }
    .dgti-btn:disabled { opacity: .7; cursor: not-allowed; }

    .dgti-spinner {
        display: none;
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255, 255, 255, .4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: dgti-spin .7s linear infinite;
    }

    @keyframes dgti-spin { to { transform: rotate(360deg); } }

    .dgti-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 24px 0 16px;
        color: var(--c-muted);
        font-size: .82rem;
    }

    .dgti-divider::before,
    .dgti-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: var(--c-border);
    }

    .dgti-links { display: flex; gap: 10px; flex-wrap: wrap; }

    .dgti-link {
        flex: 1 1 160px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 44px;
        border: 1px solid var(--c-border);
        border-radius: 10px;
        color: var(--c-text);
        text-decoration: none;
        font-size: .9rem;
        font-weight: 500;
        transition: background .15s, border-color .15s;
    }

    .dgti-link:hover { background: var(--c-bg); }

    .dgti-link--primary {
        border-color: var(--c-primary-2);
        color: var(--c-primary-2);
    }

    .dgti-link--primary:hover { background: #e8f0fc; }

    .dgti-security {
        margin: 20px 0 0;
        text-align: center;
        font-size: .78rem;
        color: var(--c-muted);
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 820px) {
        .dgti-card { grid-template-columns: 1fr; max-width: 520px; }
        .dgti-aside { padding: 24px; }
        .dgti-aside-body { margin-top: 24px; }
        .dgti-features, .dgti-tree { display: none; }
        .dgti-main { padding: 28px 22px; }
    }
</style>