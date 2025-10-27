import { Link } from "react-scroll";

export default function Navbar() {
  return (
    <nav
      style={{
        position: "fixed",
        top: 0,
        width: "100%",
        background: "rgba(18, 18, 18, 0.6)",
        backdropFilter: "blur(10px)",
        WebkitBackdropFilter: "blur(10px)",
        borderBottom: "1px solid rgba(255, 255, 255, 0.1)",
        padding: "15px 0",
        display: "flex",
        justifyContent: "center",
        gap: "40px",
        zIndex: 1000,
      }}
    >
      {["home", "about", "projects", "contact"].map((section) => (
        <Link
          key={section}
          to={section}
          smooth={true}
          duration={500}
          offset={-70}
          style={{
            color: "#f5f5f5",
            fontSize: "15px",
            cursor: "pointer",
            textTransform: "capitalize",
            textDecoration: "none",
            transition: "color 0.3s, border-bottom 0.3s",
            paddingBottom: "2px",
          }}
          activeStyle={{
            fontWeight: "600",
            borderBottom: "2px solid #a8cfff", // pastel blue accent
            color: "#a8cfff",
          }}
          onMouseEnter={(e) => (e.target.style.color = "#a8cfff")}
          onMouseLeave={(e) => (e.target.style.color = "#f5f5f5")}
        >
          {section}
        </Link>
      ))}
    </nav>
  );
}
