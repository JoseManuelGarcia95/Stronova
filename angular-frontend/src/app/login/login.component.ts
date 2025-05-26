import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { ReactiveFormsModule,FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../services/auth.service'; 

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink],
})
export class LoginComponent {
  loginForm: FormGroup;
  isSubmitting = false;
  loginError = '';

  constructor(
    private fb: FormBuilder,
    private router: Router,
    private authService: AuthService
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]]
    });
  }

  onSubmit(): void {
    if (this.loginForm.valid) {
      this.isSubmitting = true;
      this.loginError = '';
      
      const credentials = {
        email: this.loginForm.value.email,
        password: this.loginForm.value.password
      };

      this.authService.login(credentials).subscribe({
        next: (response) => {
          this.isSubmitting = false;
          console.log('Login exitoso:', response);
        },
        error: (error) => {
          this.isSubmitting = false;
          console.error('Error de inicio de sesión:', error);

          // Mostrar mensaje de error al usuario
          if (error.error && error.error.message) {
            this.loginError = error.error.message;
          } else {
            this.loginError = 'Error al iniciar sesión. Por favor, inténtelo de nuevo.';
          }
        }
      });
    } else {
      this.loginForm.markAllAsTouched();
    }
  }
}