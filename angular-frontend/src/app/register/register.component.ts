import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, AbstractControl, ValidationErrors } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Router, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink],
  templateUrl: './register.component.html',
})
export class RegisterComponent implements OnInit {
  registerForm!: FormGroup;
  isSubmitting = false;
  registerError: string | null = null;

  private apiUrl = 'http://localhost:8000/api/usuarios';

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.registerForm = this.fb.group({
      nombre: ['', [Validators.required, Validators.minLength(2)]],
      apellidos: ['', Validators.required],
      genero: ['', Validators.required],
      altura: ['', [Validators.required, Validators.min(50), Validators.max(250)]],
      peso_inicial: ['', [Validators.required, Validators.min(30), Validators.max(300)]],
      lesiones: [''],
      objetivo: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      confirmPassword: ['', Validators.required],
      terms: [false, Validators.requiredTrue]
    }, { validators: this.passwordMatchValidator });
  }

  passwordMatchValidator(group: AbstractControl): ValidationErrors | null {
    const password = group.get('password')?.value;
    const confirm = group.get('confirmPassword')?.value;
    return password === confirm ? null : { passwordMismatch: true };
  }

  getFieldError(field: string): string | null {
    const control = this.registerForm.get(field);
    if (!control || !control.touched || control.valid) return null;
    if (control.errors?.['required']) return 'Este campo es obligatorio';
    if (control.errors?.['minlength']) return `Debe tener al menos ${control.errors['minlength'].requiredLength} caracteres`;
    if (control.errors?.['email']) return 'Formato de email no válido';
    if (control.errors?.['min']) return 'Valor demasiado bajo';
    if (control.errors?.['max']) return 'Valor demasiado alto';
    if (control.errors?.['requiredTrue']) return 'Debes aceptar los términos';
    return 'Campo no válido';
  }

  onSubmit(): void {
    if (this.registerForm.invalid) {
      this.registerForm.markAllAsTouched();
      return;
    }

    this.isSubmitting = true;
    this.registerError = null;

    const formData = this.registerForm.value;
    const userData = {
        nombre: formData.nombre,
    apellidos: formData.apellidos,
    email: formData.email,
    genero: formData.genero,
    altura: Number(formData.altura),      
    peso_inicial: Number(formData.peso_inicial), 
    lesiones: formData.lesiones || "Ninguna", // Valor por defecto si está vacío
    objetivo: formData.objetivo,
    password: formData.password
    };

    console.log('Datos a enviar:', userData);

    this.http.post('http://localhost:8000/api/usuarios', userData).subscribe({
      next: (response) => {
        console.log('Registro exitoso:', response);
        this.router.navigate(['/login'], { queryParams: { registered: 'success' } });
      },
      error: (err) => {
        console.error(err);
        if (err.status === 409) {
          this.registerError = 'El email ya está registrado.';
        } else {
          this.registerError = 'Error al registrar. Intenta más tarde.';
        }
        this.isSubmitting = false;
      },
      complete: () => this.isSubmitting = false
    });
  }

  get passwordMismatch(): boolean {
    return this.registerForm.errors?.['passwordMismatch'] && this.registerForm.get('confirmPassword')?.touched;
  }
}
