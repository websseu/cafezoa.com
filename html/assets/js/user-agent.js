// user-agent.js
export function detectUserAgent() {
    const ua = navigator.userAgent.toLowerCase();
    const html = document.documentElement;

    // 초기화 (중복 방지)
    html.classList.remove(
        "is-ios", "is-android", "is-windows", "is-macos", "is-linux", "is-pc",
        "is-mobile", "is-desktop",
        "is-chrome", "is-safari", "is-firefox", "is-edge", "is-opera", "is-unknown-browser"
    );

    // OS 감지
    const isIOS = /iphone|ipad|ipod/i.test(ua);
    const isAndroid = /android/i.test(ua);
    const isWindows = /windows nt/i.test(ua);
    const isMacOS = /mac os x/i.test(ua);
    const isLinux = /linux/i.test(ua) && !isAndroid;
    const isMobile = /iphone|ipad|ipod|android|mobile/i.test(ua);

    // OS 클래스 부여
    if (isIOS) html.classList.add("is-ios");
    else if (isAndroid) html.classList.add("is-android");
    else if (isWindows) html.classList.add("is-windows");
    else if (isMacOS) html.classList.add("is-macos");
    else if (isLinux) html.classList.add("is-linux");
    else html.classList.add("is-pc");

    // 모바일 / 데스크톱 구분
    if (isMobile) html.classList.add("is-mobile");
    else html.classList.add("is-desktop");

    // 브라우저 감지
    let browserClass = "is-unknown-browser";

    if (/edg/i.test(ua)) browserClass = "is-edge";
    else if (/chrome|crios/i.test(ua)) browserClass = "is-chrome";
    else if (/firefox|fxios/i.test(ua)) browserClass = "is-firefox";
    else if (/safari/i.test(ua) && !/chrome|crios|android/i.test(ua)) browserClass = "is-safari";
    else if (/opr|opera/i.test(ua)) browserClass = "is-opera";

    html.classList.add(browserClass);

    // ==============================
    // 디버그 로그 (개발용)
    // ==============================
    const osName = isIOS
        ? "iOS"
        : isAndroid
            ? "Android"
            : isWindows
                ? "Windows"
                : isMacOS
                    ? "macOS"
                    : isLinux
                        ? "Linux"
                        : "Unknown";

    console.log("🧩 OS Detected:", osName);
    console.log("🌐 Browser Detected:", browserClass.replace("is-", ""));
    console.log("✅ Applied Classes:", html.className);
}
