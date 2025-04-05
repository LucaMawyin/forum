document.addEventListener('DOMContentLoaded', function() {
  // Password toggle functionality
  const togglePasswordButtons = document.querySelectorAll('.toggle-password');
  
  togglePasswordButtons.forEach(button => {
    button.addEventListener('click', function() {
      const passwordInput = this.previousElementSibling;
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      
      const icon = this.querySelector('i');
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    });
  });

  // Password validation for registration
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirm-password');
  const registerForm = document.getElementById('register-form');
  
  if (passwordInput && document.getElementById('length-check')) {
    const lengthCheck = document.getElementById('length-check');
    const uppercaseCheck = document.getElementById('uppercase-check');
    const lowercaseCheck = document.getElementById('lowercase-check');
    const numberCheck = document.getElementById('number-check');
    const specialCheck = document.getElementById('special-check');
    
    const validatePassword = () => {
      const password = passwordInput.value;
      
      // Check length (at least 8 characters)
      if (password.length >= 8) {
        lengthCheck.classList.add('valid');
        lengthCheck.querySelector('i').classList.replace('fa-times-circle', 'fa-check-circle');
      } else {
        lengthCheck.classList.remove('valid');
        lengthCheck.querySelector('i').classList.replace('fa-check-circle', 'fa-times-circle');
      }
      
      // Check uppercase (at least one uppercase letter)
      if (/[A-Z]/.test(password)) {
        uppercaseCheck.classList.add('valid');
        uppercaseCheck.querySelector('i').classList.replace('fa-times-circle', 'fa-check-circle');
      } else {
        uppercaseCheck.classList.remove('valid');
        uppercaseCheck.querySelector('i').classList.replace('fa-check-circle', 'fa-times-circle');
      }
      
      // Check lowercase (at least one lowercase letter)
      if (/[a-z]/.test(password)) {
        lowercaseCheck.classList.add('valid');
        lowercaseCheck.querySelector('i').classList.replace('fa-times-circle', 'fa-check-circle');
      } else {
        lowercaseCheck.classList.remove('valid');
        lowercaseCheck.querySelector('i').classList.replace('fa-check-circle', 'fa-times-circle');
      }
      
      // Check number (at least one digit)
      if (/[0-9]/.test(password)) {
        numberCheck.classList.add('valid');
        numberCheck.querySelector('i').classList.replace('fa-times-circle', 'fa-check-circle');
      } else {
        numberCheck.classList.remove('valid');
        numberCheck.querySelector('i').classList.replace('fa-check-circle', 'fa-times-circle');
      }
      
      // Check special character (at least one special character)
      if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
        specialCheck.classList.add('valid');
        specialCheck.querySelector('i').classList.replace('fa-times-circle', 'fa-check-circle');
      } else {
        specialCheck.classList.remove('valid');
        specialCheck.querySelector('i').classList.replace('fa-check-circle', 'fa-times-circle');
      }
    };
    
    passwordInput.addEventListener('input', validatePassword);
  }
  
  // Form validation
  if (registerForm && confirmPasswordInput && passwordInput) {
    registerForm.addEventListener('submit', function(event) {
      if (passwordInput.value !== confirmPasswordInput.value) {
        event.preventDefault();
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Passwords do not match.';
        
        const existingError = document.querySelector('.error-message');
        if (existingError) {
          existingError.remove();
        }
        
        registerForm.parentNode.insertBefore(errorDiv, registerForm);
        registerForm.scrollIntoView({ behavior: 'smooth' });
      }
    });
    
    confirmPasswordInput.addEventListener('input', function() {
      const existingMismatchError = document.querySelector('.password-mismatch-feedback');
      if (existingMismatchError) {
        existingMismatchError.remove();
      }
      
      if (passwordInput.value && confirmPasswordInput.value) {
        const feedbackDiv = document.createElement('div');
        feedbackDiv.className = 'password-mismatch-feedback input-help';
        
        if (passwordInput.value !== confirmPasswordInput.value) {
          feedbackDiv.textContent = 'Passwords do not match';
          feedbackDiv.style.color = 'var(--color-error)';
          confirmPasswordInput.style.borderColor = 'var(--color-error)';
        } else {
          feedbackDiv.textContent = 'Passwords match';
          feedbackDiv.style.color = 'var(--color-success)';
          confirmPasswordInput.style.borderColor = 'var(--color-success)';
        }
        
        confirmPasswordInput.parentElement.parentElement.appendChild(feedbackDiv);
      }
    });
  }
  
  // Email validation feedback
  const emailInput = document.getElementById('email');
  if (emailInput) {
    emailInput.addEventListener('input', function() {
      const existingEmailFeedback = emailInput.parentElement.parentElement.querySelector('.email-feedback');
      if (existingEmailFeedback) {
        existingEmailFeedback.remove();
      }
      
      if (emailInput.value) {
        if (!/.*@mcmaster\.ca$/.test(emailInput.value)) {
          const feedbackDiv = document.createElement('div');
          feedbackDiv.className = 'email-feedback input-help';
          feedbackDiv.textContent = 'Must be a valid McMaster email address (@mcmaster.ca)';
          feedbackDiv.style.color = 'var(--color-error)';
          emailInput.style.borderColor = 'var(--color-error)';
          
          const defaultHelp = emailInput.parentElement.parentElement.querySelector('.input-help');
          if (defaultHelp) {
            defaultHelp.after(feedbackDiv);
          } else {
            emailInput.parentElement.parentElement.appendChild(feedbackDiv);
          }
        } else {
          emailInput.style.borderColor = 'var(--color-success)';
        }
      } else {
        emailInput.style.borderColor = '';
      }
    });
  }
  
  // Username validation feedback
  const usernameInput = document.getElementById('username');
  if (usernameInput) {
    usernameInput.addEventListener('input', function() {
      const existingUsernameFeedback = usernameInput.parentElement.parentElement.querySelector('.username-feedback');
      if (existingUsernameFeedback) {
        existingUsernameFeedback.remove();
      }
      
      if (usernameInput.value) {
        const isValid = /^[a-zA-Z0-9_]{3,20}$/.test(usernameInput.value);
        
        if (!isValid) {
          const feedbackDiv = document.createElement('div');
          feedbackDiv.className = 'username-feedback input-help';
          
          if (usernameInput.value.length < 3 || usernameInput.value.length > 20) {
            feedbackDiv.textContent = 'Username must be between 3-20 characters';
          } else {
            feedbackDiv.textContent = 'Username can only contain letters, numbers, and underscores';
          }
          
          feedbackDiv.style.color = 'var(--color-error)';
          usernameInput.style.borderColor = 'var(--color-error)';
          
          const defaultHelp = usernameInput.parentElement.parentElement.querySelector('.input-help');
          if (defaultHelp) {
            defaultHelp.after(feedbackDiv);
          } else {
            usernameInput.parentElement.parentElement.appendChild(feedbackDiv);
          }
        } else {
          usernameInput.style.borderColor = 'var(--color-success)';
        }
      } else {
        usernameInput.style.borderColor = '';
      }
    });
  }
  
  // Auto-hide messages after 5 seconds
  const messages = document.querySelectorAll('.error-message, .success-message');
  
  messages.forEach(message => {
    setTimeout(() => {
      message.style.opacity = '0';
      setTimeout(() => {
        message.classList.add('hidden');
      }, 500);
    }, 5000);
  });
});