import { detectUserAgent } from "./user-agent.js";

document.addEventListener("DOMContentLoaded", () => {
    // 디바이스 환경 감지
    detectUserAgent();
});