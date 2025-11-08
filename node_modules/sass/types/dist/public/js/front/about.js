document.addEventListener("DOMContentLoaded", () => {
  // Initialize AOS animation library
  if (typeof AOS !== "undefined") {
    AOS.init({
      duration: 800,
      easing: "ease-in-out",
      once: true,
      mirror: false,
    })
  }

  // Back to top button
  const backToTopButton = document.getElementById("backToTop")

  if (backToTopButton) {
    window.addEventListener("scroll", () => {
      if (window.pageYOffset > 300) {
        backToTopButton.classList.add("active")
      } else {
        backToTopButton.classList.remove("active")
      }
    })

    backToTopButton.addEventListener("click", (e) => {
      e.preventDefault()
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      })
    })
  }

  // Testimonial Slider
  const testimonialItems = document.querySelectorAll(".testimonial-item")
  const testimonialPrev = document.querySelector(".testimonial-prev")
  const testimonialNext = document.querySelector(".testimonial-next")

  if (testimonialItems.length > 0) {
    let currentTestimonial = 0

    // Hide all testimonials except the first one
    testimonialItems.forEach((item, index) => {
      if (index !== 0) {
        item.style.display = "none"
      }
    })

    // Previous testimonial
    testimonialPrev.addEventListener("click", () => {
      testimonialItems[currentTestimonial].style.display = "none"
      currentTestimonial = (currentTestimonial - 1 + testimonialItems.length) % testimonialItems.length
      testimonialItems[currentTestimonial].style.display = "block"
    })

    // Next testimonial
    testimonialNext.addEventListener("click", () => {
      testimonialItems[currentTestimonial].style.display = "none"
      currentTestimonial = (currentTestimonial + 1) % testimonialItems.length
      testimonialItems[currentTestimonial].style.display = "block"
    })
  }

  // Counter Animation
  const counters = document.querySelectorAll(".counter-number")

  if (counters.length > 0) {
    const counterObserver = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const counter = entry.target
            const target = Number.parseInt(counter.getAttribute("data-count"))
            let count = 0
            const updateCounter = () => {
              const increment = target / 100
              if (count < target) {
                count += increment
                counter.textContent = Math.ceil(count)
                setTimeout(updateCounter, 10)
              } else {
                counter.textContent = target
              }
            }
            updateCounter()
            observer.unobserve(counter)
          }
        })
      },
      { threshold: 0.5 },
    )

    counters.forEach((counter) => {
      counterObserver.observe(counter)
    })
  }
})
