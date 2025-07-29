// src/components/MGLogo.js
export default function MGLogo() {
  return (
    <svg
      width="100%"
      height="100%"
      viewBox="0 0 200 200"
      xmlns="http://www.w3.org/2000/svg"
    >
      <defs>
        {/* Gradient hiện đại */}
        <linearGradient id="mgGradient" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stopColor="#141e30" />
          <stop offset="100%" stopColor="#243b55" />
        </linearGradient>

        {/* Hiệu ứng phát sáng nhẹ */}
        <filter id="softGlow" x="-50%" y="-50%" width="200%" height="200%">
          <feDropShadow dx="0" dy="0" stdDeviation="3" floodColor="#00eaff" floodOpacity="0.7" />
        </filter>
      </defs>

      {/* Vòng tròn viền - cao cấp tối giản */}
      <circle
        cx="100"
        cy="100"
        r="85"
        stroke="url(#mgGradient)"
        strokeWidth="6"
        fill="none"
        filter="url(#softGlow)"
      />

      {/* Chữ MG */}
      <text
        x="50%"
        y="55%"
        textAnchor="middle"
        fill="url(#mgGradient)"
        fontSize="48"
        fontWeight="bold"
        fontFamily="'Orbitron', sans-serif"
        filter="url(#softGlow)"
      >
        MG
      </text>

      {/* Tagline nhỏ bên dưới */}
      <text
        x="50%"
        y="72%"
        textAnchor="middle"
        fill="#555"
        fontSize="12"
        fontFamily="'Montserrat', sans-serif"
        letterSpacing="2px"
      >
        MAKE GREATNESS
      </text>
    </svg>
  );
}
