import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { ReactiveFormsModule,FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  imports: [CommonModule, ReactiveFormsModule]
})
export class LoginComponent {
  loginForm: FormGroup;
  isSubmitting = false;
  loginError = '';

  constructor(
    private fb: FormBuilder,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]]
    });
  }

  onSubmit(): void {
    if (this.loginForm.valid) {
      this.isSubmitting = true;
      // Aquí implementarías la lógica de autenticación real
      // Por ahora, simulamos un login exitoso después de 1 segundo
      setTimeout(() => {
        this.isSubmitting = false;
        this.router.navigate(['/dashboard']);
      }, 1000);
    } else {
      this.loginForm.markAllAsTouched();
    }
  }
}