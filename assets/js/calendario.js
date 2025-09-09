/**
 * JavaScript para el Sistema de Calendario
 * Maneja la interactividad del calendario y modal
 */

document.addEventListener("DOMContentLoaded", function () {
  // Inicializar tooltips de Bootstrap
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Variables globales
  const modalDia = document.getElementById("modalDia");
  const modalTitulo = document.getElementById("modal-titulo");
  const modalFecha = document.getElementById("modal-fecha");
  const numeroSelector = document.getElementById("numero-selector");
  const numeroValorInput = document.getElementById("numero_valor");
  const observacionesTextarea = document.getElementById("observaciones");
  const btnEliminar = document.getElementById("btn-eliminar");
  const btnLimpiar = document.getElementById("btn-limpiar");
  const formDia = document.getElementById("form-dia");

  // Datos del calendario (se llenarán desde PHP)
  let datosCalendario = {};

  // Llenar datos del calendario desde PHP
  document
    .querySelectorAll(".calendario-dia.con-datos")
    .forEach(function (dia) {
      const fecha = dia.getAttribute("data-fecha");
      const numeroElement = dia.querySelector(".dia-valor");
      const observaciones = dia.getAttribute("title") || "";

      if (numeroElement) {
        datosCalendario[fecha] = {
          numero_valor: parseInt(numeroElement.textContent),
          observaciones: observaciones,
        };
      }
    });

  // Manejar clic en números del selector
  numeroSelector.addEventListener("click", function (e) {
    if (e.target.classList.contains("numero-opcion")) {
      // Remover selección anterior
      numeroSelector
        .querySelectorAll(".numero-opcion")
        .forEach(function (opcion) {
          opcion.classList.remove("selected");
        });

      // Seleccionar nueva opción
      e.target.classList.add("selected");
      numeroValorInput.value = e.target.getAttribute("data-valor");
    }
  });

  // Manejar apertura del modal
  modalDia.addEventListener("show.bs.modal", function (event) {
    const boton = event.relatedTarget;
    const fecha = boton.getAttribute("data-fecha");
    const fechaFormateada = formatearFecha(fecha);

    // Configurar el modal
    modalTitulo.textContent = "Datos del " + fechaFormateada;
    modalFecha.value = fecha;

    // Limpiar formulario
    limpiarFormulario();

    // Si hay datos existentes, cargarlos
    if (datosCalendario[fecha]) {
      cargarDatosExistentes(fecha, datosCalendario[fecha]);
    }

    // Enfocar en el primer número
    const primerNumero = numeroSelector.querySelector(".numero-opcion");
    if (primerNumero && !numeroValorInput.value) {
      primerNumero.click();
    }
  });

  // Manejar cierre del modal
  modalDia.addEventListener("hidden.bs.modal", function () {
    limpiarFormulario();
  });

  // Manejar envío del formulario
  formDia.addEventListener("submit", function (e) {
    if (!validarFormulario()) {
      e.preventDefault();
      return false;
    }

    // Mostrar loading
    mostrarLoading();
  });

  // Manejar botón eliminar
  btnEliminar.addEventListener("click", function () {
    if (
      confirm("¿Estás seguro de que quieres eliminar los datos de este día?")
    ) {
      const form = document.createElement("form");
      form.method = "POST";
      form.innerHTML = `
                <input type="hidden" name="csrf_token" value="${
                  document.querySelector('input[name="csrf_token"]').value
                }">
                <input type="hidden" name="accion" value="eliminar_dia">
                <input type="hidden" name="fecha" value="${modalFecha.value}">
            `;

      document.body.appendChild(form);
      mostrarLoading();
      form.submit();
    }
  });

  // Manejar botón limpiar
  btnLimpiar.addEventListener("click", function () {
    if (
      confirm(
        "¿Estás seguro de que quieres limpiar todos los datos del formulario?"
      )
    ) {
      // Limpiar el formulario completamente
      limpiarFormulario();

      // Si había datos existentes, también eliminarlos de la base de datos
      const fecha = modalFecha.value;
      if (datosCalendario[fecha]) {
        const form = document.createElement("form");
        form.method = "POST";
        form.innerHTML = `
                  <input type="hidden" name="csrf_token" value="${
                    document.querySelector('input[name="csrf_token"]').value
                  }">
                  <input type="hidden" name="accion" value="eliminar_dia">
                  <input type="hidden" name="fecha" value="${fecha}">
              `;

        document.body.appendChild(form);
        mostrarLoading();
        form.submit();
      } else {
        // Si no había datos previos, solo mostrar mensaje de éxito
        mostrarExito("Formulario limpiado correctamente");
      }
    }
  });

  /**
   * Limpia el formulario del modal
   */
  function limpiarFormulario() {
    // Limpiar selector de números
    numeroSelector
      .querySelectorAll(".numero-opcion")
      .forEach(function (opcion) {
        opcion.classList.remove("selected");
      });

    numeroValorInput.value = "";
    observacionesTextarea.value = "";

    // Remover clases de validación
    formDia.classList.remove("was-validated");

    // Ocultar botón eliminar
    btnEliminar.style.display = "none";
  }

  /**
   * Carga datos existentes en el formulario
   * @param {string} fecha
   * @param {object} datos
   */
  function cargarDatosExistentes(fecha, datos) {
    // Seleccionar número
    const numeroOpcion = numeroSelector.querySelector(
      `[data-valor="${datos.numero_valor}"]`
    );
    if (numeroOpcion) {
      numeroOpcion.click();
    }

    // Cargar observaciones
    if (datos.observaciones) {
      observacionesTextarea.value = datos.observaciones;
    }

    // Mostrar botón eliminar si hay datos existentes
    btnEliminar.style.display = "inline-block";
  }

  /**
   * Valida el formulario antes del envío
   * @returns {boolean}
   */
  function validarFormulario() {
    let esValido = true;

    // Validar número seleccionado
    if (!numeroValorInput.value) {
      mostrarError("Debe seleccionar un número del 1 al 5");
      esValido = false;
    }

    // Validar fecha
    if (!modalFecha.value) {
      mostrarError("Fecha no válida");
      esValido = false;
    }

    return esValido;
  }

  /**
   * Formatea una fecha para mostrar
   * @param {string} fecha - Fecha en formato Y-m-d
   * @returns {string}
   */
  function formatearFecha(fecha) {
    const [anio, mes, dia] = fecha.split("-");
    const meses = [
      "Enero",
      "Febrero",
      "Marzo",
      "Abril",
      "Mayo",
      "Junio",
      "Julio",
      "Agosto",
      "Septiembre",
      "Octubre",
      "Noviembre",
      "Diciembre",
    ];

    return `${parseInt(dia)} de ${meses[parseInt(mes) - 1]} de ${anio}`;
  }

  /**
   * Muestra un mensaje de error
   * @param {string} mensaje
   */
  function mostrarError(mensaje) {
    // Crear alert temporal
    const alert = document.createElement("div");
    alert.className = "alert alert-danger alert-dismissible fade show";
    alert.innerHTML = `
            <i class="bi bi-exclamation-triangle-fill me-2"></i>${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    // Insertar al inicio del modal-body
    const modalBody = modalDia.querySelector(".modal-body");
    modalBody.insertBefore(alert, modalBody.firstChild);

    // Auto-ocultar después de 5 segundos
    setTimeout(function () {
      if (alert.parentNode) {
        alert.remove();
      }
    }, 5000);
  }

  /**
   * Muestra un mensaje de éxito flotante
   * @param {string} mensaje
   */
  function mostrarExito(mensaje) {
    // Crear notificación flotante
    const notification = document.createElement("div");
    notification.className = "toast-notification success auto-fade";
    notification.innerHTML = `
            <span><i class="bi bi-check-circle-fill me-2"></i>${mensaje}</span>
            <button type="button" class="btn-close" onclick="this.parentElement.remove()">×</button>
        `;

    // Agregar al body
    document.body.appendChild(notification);

    // Auto-fade después de 2 segundos
    setTimeout(function () {
      if (notification.parentNode) {
        // Agregar transición y fade out
        notification.style.transition =
          "opacity 0.5s ease-out, transform 0.5s ease-out";
        notification.style.opacity = "0";
        notification.style.transform = "translateX(-50%) translateY(-20px)";

        // Remover después del fade out
        setTimeout(function () {
          if (notification.parentNode) {
            notification.remove();
          }
        }, 500);
      }
    }, 2000);
  }

  /**
   * Muestra loading overlay
   */
  function mostrarLoading() {
    // Crear overlay de loading si no existe
    let overlay = document.getElementById("loading-overlay");
    if (!overlay) {
      overlay = document.createElement("div");
      overlay.id = "loading-overlay";
      overlay.className = "spinner-overlay";
      overlay.innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            `;
      document.body.appendChild(overlay);
    }

    overlay.style.display = "flex";
  }

  /**
   * Oculta loading overlay
   */
  function ocultarLoading() {
    const overlay = document.getElementById("loading-overlay");
    if (overlay) {
      overlay.style.display = "none";
    }
  }

  // Manejar teclas en el modal
  modalDia.addEventListener("keydown", function (e) {
    // Esc para cerrar
    if (e.key === "Escape") {
      bootstrap.Modal.getInstance(modalDia).hide();
      return;
    }

    // Números 1-5 para selección rápida (solo si NO estamos en el textarea)
    if (e.key >= "1" && e.key <= "5" && e.target !== observacionesTextarea) {
      const numero = parseInt(e.key);
      const opcion = numeroSelector.querySelector(`[data-valor="${numero}"]`);
      if (opcion) {
        opcion.click();
        e.preventDefault();
      }
    }

    // Enter para guardar (si está en el textarea o en el modal)
    if (e.key === "Enter" && !e.shiftKey) {
      if (e.target === observacionesTextarea) {
        // En el textarea, permitir Enter normal con Shift+Enter para nueva línea
        return;
      }

      // En cualquier otro lugar del modal, guardar
      if (validarFormulario()) {
        formDia.submit();
      }
      e.preventDefault();
    }
  });

  // Manejar clic en días del calendario con teclado
  document.addEventListener("keydown", function (e) {
    // Si el modal está abierto, no manejar estas teclas
    if (modalDia.classList.contains("show")) {
      return;
    }

    // Flecha izquierda/derecha para navegar meses
    if (e.key === "ArrowLeft" && e.ctrlKey) {
      const btnAnterior = document.querySelector('a[href*="mes="]');
      if (btnAnterior) {
        window.location.href = btnAnterior.href;
      }
      e.preventDefault();
    }

    if (e.key === "ArrowRight" && e.ctrlKey) {
      const btnSiguiente = document.querySelector(
        'a[href*="mes="]:last-of-type'
      );
      if (btnSiguiente) {
        window.location.href = btnSiguiente.href;
      }
      e.preventDefault();
    }
  });

  // Auto-ocultar alertas después de 10 segundos (excepto las de auto-fade)
  setTimeout(function () {
    document
      .querySelectorAll(".alert-dismissible:not(.auto-fade)")
      .forEach(function (alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      });
  }, 10000);

  // Efectos de hover mejorados
  document.querySelectorAll(".calendario-dia").forEach(function (dia) {
    dia.addEventListener("mouseenter", function () {
      this.style.transform = "scale(1.02)";
    });

    dia.addEventListener("mouseleave", function () {
      this.style.transform = "scale(1)";
    });
  });

  console.log("Sistema de Calendario inicializado correctamente");
});
