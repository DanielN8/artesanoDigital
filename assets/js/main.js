/**
 * JavaScript principal del sistema
 * Responsabilidad: Funcionalidades generales y manejo de UI
 */

// Configuración global
const CONFIG = {
  API_BASE_URL: "/artesanoDigital/api",
  TOAST_DURATION: 5000,
  DEBOUNCE_DELAY: 300,
}

// Utilidades generales
const Utils = {
  /**
   * Debounce function para optimizar llamadas
   */
  debounce(func, wait) {
    let timeout
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout)
        func(...args)
      }
      clearTimeout(timeout)
      timeout = setTimeout(later, wait)
    }
  },

  /**
   * Sanitiza HTML para prevenir XSS
   */
  sanitizeHTML(str) {
    const temp = document.createElement("div")
    temp.textContent = str
    return temp.innerHTML
  },

  /**
   * Formatea precio en formato panameño
   */
  formatPrice(price) {
    return new Intl.NumberFormat("es-PA", {
      style: "currency",
      currency: "USD",
    }).format(price)
  },

  /**
   * Formatea fecha en formato local
   */
  formatDate(date) {
    return new Intl.DateTimeFormat("es-PA", {
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    }).format(new Date(date))
  },

  /**
   * Valida email
   */
  validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return re.test(email)
  },

  /**
   * Valida teléfono panameño
   */
  validatePhone(phone) {
    const re = /^(\+507-)?[6-9]\d{3}-\d{4}$/
    return re.test(phone)
  },
}

// Sistema de notificaciones Toast
const Toast = {
  container: null,

  init() {
    this.container = document.getElementById("toast-container")
    if (!this.container) {
      this.container = document.createElement("div")
      this.container.id = "toast-container"
      this.container.className = "toast-container"
      document.body.appendChild(this.container)
    }
  },

  show(message, type = "info", duration = CONFIG.TOAST_DURATION) {
    if (!this.container) this.init()

    const toast = document.createElement("div")
    toast.className = `toast ${type}`
    toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-message">${Utils.sanitizeHTML(message)}</span>
                <button class="toast-close" onclick="this.parentElement.parentElement.remove()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        `

    this.container.appendChild(toast)

    // Auto-remove después del tiempo especificado
    setTimeout(() => {
      if (toast.parentElement) {
        toast.remove()
      }
    }, duration)

    return toast
  },

  success(message, duration) {
    return this.show(message, "exito", duration)
  },

  error(message, duration) {
    return this.show(message, "error", duration)
  },

  warning(message, duration) {
    return this.show(message, "advertencia", duration)
  },

  info(message, duration) {
    return this.show(message, "info", duration)
  },
}

// Manejo de formularios
const FormHandler = {
  /**
   * Inicializa validación de formularios
   */
  init() {
    document.addEventListener("submit", this.handleSubmit.bind(this))
    document.addEventListener("input", this.handleInput.bind(this))
  },

  /**
   * Maneja envío de formularios
   */
  async handleSubmit(event) {
    const form = event.target
    if (!form.matches("form[data-ajax]")) return

    event.preventDefault()

    const submitBtn = form.querySelector('button[type="submit"]')
    const originalText = submitBtn?.textContent

    try {
      // Mostrar estado de carga
      if (submitBtn) {
        submitBtn.disabled = true
        submitBtn.innerHTML = '<span class="spinner"></span> Procesando...'
      }

      const formData = new FormData(form)
      const response = await fetch(form.action, {
        method: form.method || "POST",
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })

      const result = await response.json()

      if (result.exitoso) {
        Toast.success(result.mensaje)

        // Redirigir si se especifica
        if (result.redirigir) {
          setTimeout(() => {
            window.location.href = result.redirigir
          }, 1000)
        }

        // Resetear formulario si es necesario
        if (form.dataset.reset !== "false") {
          form.reset()
        }
      } else {
        Toast.error(result.mensaje)
      }
    } catch (error) {
      console.error("Error en formulario:", error)
      Toast.error("Error de conexión. Inténtalo de nuevo.")
    } finally {
      // Restaurar botón
      if (submitBtn) {
        submitBtn.disabled = false
        submitBtn.textContent = originalText
      }
    }
  },

  /**
   * Validación en tiempo real
   */
  handleInput(event) {
    const input = event.target
    if (!input.matches("input, textarea, select")) return

    this.validateField(input)
  },

  /**
   * Valida un campo individual
   */
  validateField(field) {
    const value = field.value.trim()
    let isValid = true
    let errorMessage = ""

    // Validaciones específicas por tipo
    switch (field.type) {
      case "email":
        if (value && !Utils.validateEmail(value)) {
          isValid = false
          errorMessage = "Formato de correo inválido"
        }
        break

      case "tel":
        if (value && !Utils.validatePhone(value)) {
          isValid = false
          errorMessage = "Formato de teléfono inválido (ej: 6123-4567)"
        }
        break

      case "password":
        if (value && value.length < 8) {
          isValid = false
          errorMessage = "La contraseña debe tener al menos 8 caracteres"
        }
        break
    }

    // Validación de campos requeridos
    if (field.required && !value) {
      isValid = false
      errorMessage = "Este campo es requerido"
    }

    // Mostrar/ocultar mensaje de error
    this.showFieldError(field, isValid ? "" : errorMessage)

    return isValid
  },

  /**
   * Muestra error en campo
   */
  showFieldError(field, message) {
    let errorElement = field.parentElement.querySelector(".form-error")

    if (message) {
      if (!errorElement) {
        errorElement = document.createElement("div")
        errorElement.className = "form-error"
        field.parentElement.appendChild(errorElement)
      }
      errorElement.textContent = message
      field.classList.add("error")
    } else {
      if (errorElement) {
        errorElement.remove()
      }
      field.classList.remove("error")
    }
  },
}

// Manejo de navegación
const Navigation = {
  init() {
    this.initDropdowns()
    this.initMobileMenu()
    this.initNotifications()
    this.updateCartCounter()
  },

  /**
   * Inicializa dropdowns
   */
  initDropdowns() {
    document.addEventListener("click", (event) => {
      // Cerrar todos los dropdowns
      document.querySelectorAll(".dropdown-menu, .dropdown-notificaciones").forEach((dropdown) => {
        dropdown.classList.remove("activo")
      })

      // Abrir dropdown clickeado
      const trigger = event.target.closest("[data-dropdown]")
      if (trigger) {
        event.preventDefault()
        const targetId = trigger.dataset.dropdown
        const dropdown = document.getElementById(targetId)
        if (dropdown) {
          dropdown.classList.add("activo")
        }
      }
    })
  },

  /**
   * Inicializa menú móvil
   */
  initMobileMenu() {
    const btnMenuMovil = document.getElementById('btnMenuMovil');
    const navbarNav = document.querySelector('.navbar-nav');
    
    if (btnMenuMovil && navbarNav) {
      btnMenuMovil.addEventListener('click', () => {
        navbarNav.classList.toggle('activo');
        btnMenuMovil.classList.toggle('activo');
      });
      
      // Cerrar menú al hacer click en un enlace
      navbarNav.addEventListener('click', (e) => {
        if (e.target.classList.contains('nav-link')) {
          navbarNav.classList.remove('activo');
          btnMenuMovil.classList.remove('activo');
        }
      });
      
      // Cerrar menú al hacer click fuera
      document.addEventListener('click', (e) => {
        if (!btnMenuMovil.contains(e.target) && !navbarNav.contains(e.target)) {
          navbarNav.classList.remove('activo');
          btnMenuMovil.classList.remove('activo');
        }
      });
    }
  },

  /**
   * Inicializa sistema de notificaciones
   */
  initNotifications() {
    this.loadNotifications()

    // Actualizar notificaciones cada 30 segundos
    setInterval(() => {
      this.loadNotifications()
    }, 30000)
  },

  /**
   * Carga notificaciones del servidor
   */
  async loadNotifications() {
    try {
      const response = await fetch(`${CONFIG.API_BASE_URL}/notificaciones`)
      if (!response.ok) return

      const data = await response.json()
      this.updateNotificationUI(data.notificaciones || [])
    } catch (error) {
      console.error("Error cargando notificaciones:", error)
    }
  },

  /**
   * Actualiza UI de notificaciones
   */
  updateNotificationUI(notifications) {
    const contador = document.getElementById("contadorNotificaciones")
    const lista = document.getElementById("listaNotificaciones")

    if (!contador || !lista) return

    const noLeidas = notifications.filter((n) => !n.leida)

    // Actualizar contador
    if (noLeidas.length > 0) {
      contador.textContent = noLeidas.length
      contador.style.display = "flex"
    } else {
      contador.style.display = "none"
    }

    // Actualizar lista
    if (notifications.length === 0) {
      lista.innerHTML = '<div class="notificacion-vacia">No hay notificaciones nuevas</div>'
    } else {
      lista.innerHTML = notifications
        .map(
          (notification) => `
                <div class="notificacion-item ${!notification.leida ? "no-leida" : ""}" 
                     data-id="${notification.id_notificacion}"
                     onclick="Navigation.markNotificationAsRead(${notification.id_notificacion})">
                    <div class="notificacion-mensaje">${Utils.sanitizeHTML(notification.mensaje)}</div>
                    <div class="notificacion-fecha">${Utils.formatDate(notification.fecha_creacion)}</div>
                </div>
            `,
        )
        .join("")
    }
  },

  /**
   * Marca notificación como leída
   */
  async markNotificationAsRead(id) {
    try {
      const response = await fetch(`${CONFIG.API_BASE_URL}/notificaciones/marcar-leida`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ id_notificacion: id }),
      })

      if (response.ok) {
        this.loadNotifications()
      }
    } catch (error) {
      console.error("Error marcando notificación:", error)
    }
  },

  /**
   * Actualiza contador del carrito
   */
  async updateCartCounter() {
    const contador = document.getElementById("contadorCarrito")
    if (!contador) return

    try {
      const response = await fetch(`${CONFIG.API_BASE_URL}/carrito/contador`)
      if (!response.ok) return

      const data = await response.json()
      contador.textContent = data.cantidad || 0
    } catch (error) {
      console.error("Error actualizando contador carrito:", error)
    }
  },
}

// Manejo de productos
const ProductHandler = {
  init() {
    this.initFilters()
    this.initSearch()
    this.initCartActions()
  },

  /**
   * Inicializa filtros de productos
   */
  initFilters() {
    const filtros = document.querySelectorAll("[data-filter]")
    filtros.forEach((filtro) => {
      filtro.addEventListener(
        "change",
        Utils.debounce(() => {
          this.applyFilters()
        }, CONFIG.DEBOUNCE_DELAY),
      )
    })
  },

  /**
   * Inicializa búsqueda
   */
  initSearch() {
    const searchInput = document.querySelector("[data-search]")
    if (searchInput) {
      searchInput.addEventListener(
        "input",
        Utils.debounce((event) => {
          this.performSearch(event.target.value)
        }, CONFIG.DEBOUNCE_DELAY),
      )
    }
  },

  /**
   * Inicializa acciones del carrito
   */
  initCartActions() {
    document.addEventListener("click", (event) => {
      const btn = event.target.closest("[data-add-to-cart]")
      if (btn) {
        event.preventDefault()
        const productId = btn.dataset.addToCart
        this.addToCart(productId)
      }
    })
  },

  /**
   * Aplica filtros de productos
   */
  async applyFilters() {
    const filtros = {}
    document.querySelectorAll("[data-filter]").forEach((filtro) => {
      if (filtro.value) {
        filtros[filtro.name] = filtro.value
      }
    })

    try {
      const params = new URLSearchParams(filtros)
      const response = await fetch(`/productos?${params}`)
      const html = await response.text()

      const parser = new DOMParser()
      const doc = parser.parseFromString(html, "text/html")
      const newGrid = doc.querySelector(".productos-grid")

      if (newGrid) {
        document.querySelector(".productos-grid").innerHTML = newGrid.innerHTML
      }
    } catch (error) {
      console.error("Error aplicando filtros:", error)
    }
  },

  /**
   * Realiza búsqueda de productos
   */
  async performSearch(query) {
    if (query.length < 2) return

    try {
      const response = await fetch(`/productos?busqueda=${encodeURIComponent(query)}`)
      const html = await response.text()

      const parser = new DOMParser()
      const doc = parser.parseFromString(html, "text/html")
      const newGrid = doc.querySelector(".productos-grid")

      if (newGrid) {
        document.querySelector(".productos-grid").innerHTML = newGrid.innerHTML
      }
    } catch (error) {
      console.error("Error en búsqueda:", error)
    }
  },

  /**
   * Añade producto al carrito
   */
  async addToCart(productId, quantity = 1) {
    try {
      const response = await fetch("/carrito/agregar", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({
          id_producto: productId,
          cantidad: quantity,
        }),
      })

      const result = await response.json()

      if (result.exitoso) {
        Toast.success(result.mensaje)
        Navigation.updateCartCounter()
      } else {
        Toast.error(result.mensaje)
      }
    } catch (error) {
      console.error("Error añadiendo al carrito:", error)
      Toast.error("Error de conexión. Inténtalo de nuevo.")
    }
  },
}

// Menú móvil
const MobileMenu = {
  init() {
    const btnMenuMovil = document.getElementById('btnMenuMovil');
    const navbarNav = document.querySelector('.navbar-nav');
    
    if (btnMenuMovil && navbarNav) {
      btnMenuMovil.addEventListener('click', () => {
        navbarNav.classList.toggle('activo');
        btnMenuMovil.classList.toggle('activo');
      });
      
      // Cerrar menú al hacer click en un enlace
      navbarNav.addEventListener('click', (e) => {
        if (e.target.classList.contains('nav-link')) {
          navbarNav.classList.remove('activo');
          btnMenuMovil.classList.remove('activo');
        }
      });
      
      // Cerrar menú al hacer click fuera
      document.addEventListener('click', (e) => {
        if (!btnMenuMovil.contains(e.target) && !navbarNav.contains(e.target)) {
          navbarNav.classList.remove('activo');
          btnMenuMovil.classList.remove('activo');
        }
      });
    }
  }
};

// Inicialización cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  Toast.init()
  FormHandler.init()
  Navigation.init()
  ProductHandler.init()
  MobileMenu.init()

  // Configurar botones con data-dropdown
  document.getElementById("btnNotificaciones")?.setAttribute("data-dropdown", "dropdownNotificaciones")
  document.getElementById("btnUsuario")?.setAttribute("data-dropdown", "dropdownUsuario")
})

// Exportar para uso global
window.Utils = Utils
window.Toast = Toast
window.FormHandler = FormHandler
window.Navigation = Navigation
window.ProductHandler = ProductHandler
