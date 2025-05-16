var tablinks = document.getElementsByClassName("tab-links");
var tabcontents = document.getElementsByClassName("tab-contents");

function opentab(tabname) {
    for(tablink of tablinks) {
        tablink.classList.remove("active-link");
    }
    for(tabcontent of tabcontents) {
        tabcontent.classList.remove("active-tab");
    } 
    event.currentTarget.classList.add("active-link");
    document.getElementById(tabname).classList.add("active-tab");
}

// Make the navbar responsive
window.addEventListener('scroll', function() {
    let header = document.querySelector('header');
    header.classList.toggle('sticky', window.scrollY > 100);
    
    // Update active menu based on scroll position
    let sections = document.querySelectorAll('section');
    let navLinks = document.querySelectorAll('.navbar a');
    
    sections.forEach(section => {
        let top = window.scrollY;
        let offset = section.offsetTop - 150;
        let height = section.offsetHeight;
        let id = section.getAttribute('id');
        
        if(top >= offset && top < offset + height) {
            navLinks.forEach(link => {
                link.classList.remove('active');
                if(link.getAttribute('href') === '#' + id) {
                    link.classList.add('active');
                }
            });
        }
    });
});

// Skill bars animation
window.addEventListener('load', function() {
    let skillSection = document.querySelector('.skills');
    let skillBars = document.querySelectorAll('.skill-level');
    
    // Function to check if element is in viewport
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    // Function to animate skill bars when in viewport
    function animateSkillBars() {
        if(isInViewport(skillSection)) {
            skillBars.forEach(bar => {
                bar.style.width = bar.textContent;
            });
            window.removeEventListener('scroll', animateSkillBars);
        }
    }
    
    // Initial check on page load
    animateSkillBars();
    
    // Check on scroll
    window.addEventListener('scroll', animateSkillBars);
});

// Form submission handling
const contactForm = document.querySelector('.contact-form form');
if(contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const name = this.querySelector('input[placeholder="Full Name"]').value;
        const email = this.querySelector('input[placeholder="Email Address"]').value;
        const subject = this.querySelector('input[placeholder="Subject"]').value;
        const message = this.querySelector('textarea').value;
        
        // Basic validation
        if(!name || !email || !subject || !message) {
            alert('Please fill all fields');
            return false;
        }
        
        // Here you would typically send the form data to a server
        // For now, we'll just show a success message
        alert('Thank you for your message, ' + name + '! I will get back to you soon.');
        this.reset();
    });
}