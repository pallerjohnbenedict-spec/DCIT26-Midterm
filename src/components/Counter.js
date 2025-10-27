import { useState, useEffect } from "react";

export default function Counter() {
  const [count, setCount] = useState(0);

  // useEffect example: log when count changes
  useEffect(() => {
    console.log(`You clicked ${count} times`);
  }, [count]);

  return (
    <div style={{ marginTop: "20px", textAlign: "center" }}>
      <h3>You clicked {count} times</h3>
      <button
        onClick={() => setCount(count + 1)}
        style={{
          background: "#a8cfff",
          color: "#121212",
          border: "none",
          padding: "10px 20px",
          borderRadius: "8px",
          cursor: "pointer",
          fontWeight: "600",
          transition: "0.3s",
        }}
      >
        Click Me
      </button>
    </div>
  );
}
