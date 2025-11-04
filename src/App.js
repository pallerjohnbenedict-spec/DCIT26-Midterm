import Navbar from "./components/Navbar";
import myPhoto from "./assets/me.jpg";
import ss2 from "./assets/ss2.png";
import { motion } from "framer-motion";
import ss1 from "./assets/ss1.png";

export default function App() {
  const sectionStyle = {
    minHeight: "100vh",
    display: "flex",
    flexDirection: "column",
    alignItems: "center",
    justifyContent: "center",
    padding: "80px 20px",
    background: "#121212",
    color: "white",
  };

  const title = {
    fontSize: "2.5rem",
    marginBottom: "20px",
    color: "#a8cfff",
    textAlign: "center",
  };

  const text = {
    maxWidth: "700px",
    textAlign: "center",
    color: "#d9d9d9",
    lineHeight: "1.6",
    marginBottom: "40px",
  };

  return (
    <div
      style={{
        fontFamily: "Inter, sans-serif",
        background: "#121212",
        color: "white",
      }}
    >
      <Navbar />

      {/* HOME */}
      <section
        id="home"
        style={{
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          minHeight: "100vh",
          background: "#121212",
          color: "white",
          gap: "60px",
          flexWrap: "wrap",
          padding: "0 40px",
          overflow: "hidden",
        }}
      >
        <motion.img
          src={myPhoto}
          alt="John Benedict Paller"
          initial={{ opacity: 0, y: 50 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 1, ease: "easeOut" }}
          whileHover={{ scale: 1.05, rotate: 1 }}
          style={{
            width: "350px",
            height: "470px",
            objectFit: "cover",
            boxShadow: "0 6px 18px rgba(0,0,0,0.5)",
            borderRadius: "10px",
          }}
        />

        <motion.div
          initial={{ opacity: 0, x: 60 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 1, delay: 0.4, ease: "easeOut" }}
          style={{ maxWidth: "500px", textAlign: "left" }}
        >
          <motion.h1
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.6 }}
            style={{
              fontSize: "2.5rem",
              marginBottom: "15px",
              color: "#a8cfff",
            }}
          >
            Hi, I’m John Benedict Paller
          </motion.h1>

          <motion.p
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 1, delay: 0.8 }}
            style={{
              color: "#d9d9d9",
              lineHeight: "1.6",
              fontSize: "1rem",
            }}
          >
            22-year-old Computer Science student from Parañaque City. I’m
            passionate about technology and software development, always eager
            to learn new things and build creative digital projects. I love
            traveling, playing Mobile Legends, and spending time with my pets —
            my dog and my cat named Siopao.
          </motion.p>
        </motion.div>
      </section>

      {/* ABOUT */}
      <section
        id="about"
        style={{
          position: "relative",
          overflow: "hidden",
          background: "#121212",
          color: "white",
          padding: "100px 20px",
          textAlign: "center",
        }}
      >
        <motion.h2
          style={{
            fontSize: "2.5rem",
            marginBottom: "40px",
            color: "#60a5fa",
            position: "relative",
            zIndex: 1,
          }}
          initial={{ opacity: 0, y: -30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, ease: "easeOut" }}
          viewport={{ once: true }}
        >
          About Me
        </motion.h2>

        <motion.p
          style={{
            maxWidth: "700px",
            margin: "0 auto 60px",
            fontSize: "1.1rem",
            color: "#d1d5db",
            lineHeight: "1.6",
            position: "relative",
            zIndex: 1,
          }}
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
          viewport={{ once: true }}
        >
          Language I used:
        </motion.p>

        <div
          style={{
            display: "flex",
            flexWrap: "wrap",
            justifyContent: "center",
            gap: "30px",
            position: "relative",
            zIndex: 1,
          }}
        >
          {[
            {
              title: "Java",
              icon: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/java/java-original.svg",
            },
            {
              title: "Python",
              icon: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/python/python-original.svg",
            },
            {
              title: "JavaScript",
              icon: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg",
            },
            {
              title: "PHP",
              icon: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg",
            },
          ].map((skill, index) => (
            <motion.div
              key={skill.title}
              initial={{ opacity: 0, y: 40 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{
                duration: 0.6,
                delay: index * 0.2,
                ease: "easeOut",
              }}
              viewport={{ once: true }}
              whileHover={{
                scale: 1.1,
                y: -5,
                boxShadow: "0 0 25px rgba(96,165,250,0.7)",
                border: "1px solid #60a5fa",
              }}
              style={{
                background: "rgba(20, 20, 20, 0.9)",
                borderRadius: "12px",
                padding: "25px",
                width: "130px",
                height: "130px",
                display: "flex",
                flexDirection: "column",
                alignItems: "center",
                justifyContent: "center",
                boxShadow: "0 4px 10px rgba(0,0,0,0.6)",
                backdropFilter: "blur(6px)",
                border: "1px solid transparent",
                transition: "all 0.3s ease",
              }}
            >
              <img
                src={skill.icon}
                alt={skill.title}
                style={{ width: "60px", height: "60px", marginBottom: "10px" }}
              />
              <h3 style={{ fontSize: "1rem", color: "#e5e7eb" }}>
                {skill.title}
              </h3>
            </motion.div>
          ))}
        </div>
      </section>

      {/* PROJECTS SECTION */}
      <motion.h2
        style={{
          fontSize: "2.5rem",
          marginBottom: "40px",
          color: "#60a5fa",
          position: "relative",
          zIndex: 1,
          textAlign: "center",
        }}
        initial={{ opacity: 0, y: -30 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.6, ease: "easeOut" }}
        viewport={{ once: true }}
      >
        My Projects
      </motion.h2>

      <section
        id="projects"
        style={{
          background: "#121212",
          color: "white",
          padding: "100px 20px",
          textAlign: "center",
          display: "flex",
          justifyContent: "center",
          alignItems: "flex-start",
          gap: "40px",
          flexWrap: "wrap",
        }}
      >
        <motion.div
          whileHover={{
            scale: 1.05,
            boxShadow: "0 0 25px rgba(96,165,250,0.5)",
          }}
          transition={{ type: "spring", stiffness: 200 }}
          style={{
            background: "#1e293b",
            borderRadius: "12px",
            padding: "20px",
            width: "300px",
            textAlign: "center",
            color: "#d1d5db",
          }}
        >
          <img
            src={ss1}
            alt="Project A Preview"
            style={{
              width: "100%",
              height: "180px",
              objectFit: "cover",
              borderRadius: "8px",
              marginBottom: "10px",
            }}
          />

          <h3
            style={{
              fontSize: "1.3rem",
              color: "#a8cfff",
              marginBottom: "10px",
            }}
          >
            CvSU Online Appointment
          </h3>

          <a
            href="/updated/homepage.html"
            target="_blank"
            rel="noopener noreferrer"
            style={{
              display: "inline-block",
              marginTop: "10px",
              background: "#60a5fa",
              color: "#121212",
              padding: "10px 15px",
              borderRadius: "8px",
              fontWeight: "bold",
              textDecoration: "none",
              transition: "background 0.3s",
            }}
            onMouseEnter={(e) => (e.target.style.background = "#93c5fd")}
            onMouseLeave={(e) => (e.target.style.background = "#60a5fa")}
          >
            🔗 Open homepage.html
          </a>
        </motion.div>

        <motion.div
          whileHover={{
            scale: 1.05,
            boxShadow: "0 0 25px rgba(96,165,250,0.5)",
          }}
          transition={{ type: "spring", stiffness: 200 }}
          style={{
            background: "#1e293b",
            borderRadius: "12px",
            padding: "20px",
            width: "300px",
            textAlign: "center",
            color: "#d1d5db",
          }}
        >
           <img
            src={ss2}
            alt="Project A Preview"
            style={{
              width: "100%",
              height: "180px",
              objectFit: "cover",
              borderRadius: "8px",
              marginBottom: "10px",
            }}
          />
          <h3
            style={{
              fontSize: "1.3rem",
              color: "#a8cfff",
              marginBottom: "10px",
            }}
          >
            To-Do List App
          </h3>
          <a
            href="/ToDoApp/index.html"
            target="_blank"
            rel="noopener noreferrer"
            style={{
              display: "inline-block",
              marginTop: "10px",
              background: "#60a5fa",
              color: "#121212",
              padding: "10px 15px",
              borderRadius: "8px",
              fontWeight: "bold",
              textDecoration: "none",
              transition: "background 0.3s",
            }}
            onMouseEnter={(e) => (e.target.style.background = "#93c5fd")}
            onMouseLeave={(e) => (e.target.style.background = "#60a5fa")}
          >
            🔗 View To-Do App
          </a>
        </motion.div>
      </section>

      {/* CONTACT */}
      <section
        id="contact"
        style={{
          backgroundColor: "#121212",
          color: "white",
          padding: "80px 20px",
          display: "flex",
          justifyContent: "center",
          alignItems: "center",
          overflow: "hidden",
        }}
      >
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, ease: "easeOut" }}
          viewport={{ once: true }}
          style={{
            display: "flex",
            alignItems: "center",
            justifyContent: "space-between",
            gap: "40px",
            maxWidth: "800px",
            width: "100%",
            flexWrap: "wrap",
          }}
        >
          <motion.div
            initial={{ x: -80, opacity: 0 }}
            whileInView={{ x: 0, opacity: 1 }}
            transition={{ duration: 0.8, delay: 0.2 }}
            style={{ flex: "1", minWidth: "280px" }}
          >
            <h2
              style={{
                fontSize: "2.5rem",
                fontWeight: "bold",
                color: "#a8cfff",
                marginBottom: "30px",
              }}
            >
              Contact Me!
            </h2>

            <div
              style={{
                display: "flex",
                flexDirection: "column",
                gap: "20px",
                fontSize: "1.1rem",
              }}
            >
              {[
                {
                  icon: "📧",
                  text: "pallerjohnbenedict@gmail.com",
                  href: "https://mail.google.com/mail/u/0/#inbox",
                },
                {
                  icon: "🐈",
                  text: "github.com/pallerjohnbenedict-spec",
                  href: "https://github.com/pallerjohnbenedict-spec",
                },
                {
                  icon: "💬",
                  text: "facebook.com/jbcpaller",
                  href: "https://facebook.com/jbcpaller",
                },
                {
                  icon: "📸",
                  text: "@cozy_jb",
                  href: "https://instagram.com/cozy_jb",
                },
                
              ].map((item, index) => (
                <motion.a
                  key={item.text}
                  href={item.href}
                  target="_blank"
                  rel="noopener noreferrer"
                  initial={{ opacity: 0, x: -30 }}
                  whileInView={{ opacity: 1, x: 0 }}
                  transition={{
                    duration: 0.5,
                    delay: 0.4 + index * 0.15,
                  }}
                  style={{
                    display: "flex",
                    alignItems: "center",
                    gap: "10px",
                    color: "#ddd",
                    textDecoration: "none",
                    transition: "color 0.3s ease",
                  }}
                >
                  {item.icon} {item.text}
                </motion.a>
              ))}
            </div>
          </motion.div>

          <motion.div
            initial={{ x: 80, opacity: 0 }}
            whileInView={{ x: 0, opacity: 1 }}
            transition={{ duration: 0.8, delay: 0.4 }}
            style={{ flex: "1", minWidth: "280px", textAlign: "center" }}
          >
            <p
              style={{
                color: "#ddd",
                fontSize: "1rem",
                marginBottom: "20px",
              }}
            >
              Let’s collaborate or connect!
            </p>

            <motion.button
              whileHover={{
                scale: 1.08,
                backgroundColor: "#cfe4ff",
              }}
              whileTap={{ scale: 0.95 }}
              transition={{ type: "spring", stiffness: 300 }}
              style={{
                backgroundColor: "#a8cfff",
                color: "#0e0e0e",
                padding: "12px 24px",
                borderRadius: "8px",
                border: "none",
                cursor: "pointer",
                fontWeight: "bold",
              }}
            >
              Send a Message
            </motion.button>
          </motion.div>
        </motion.div>
      </section>

      <footer
        style={{
          textAlign: "center",
          padding: "20px",
          background: "#121212",
          color: "#bbb",
          fontSize: "14px",
          borderTop: "1px solid #2a2a2a",
        }}
      >
        © {new Date().getFullYear()} JB Paller. All rights reserved.
      </footer>
    </div>
  );
}
