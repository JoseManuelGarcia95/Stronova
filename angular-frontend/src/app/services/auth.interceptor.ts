import { Injectable } from "@angular/core";
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent } from "@angular/common/http";
import { Observable } from "rxjs";
import { AuthService } from "./auth.service";

@Injectable() 
export class AuthInterceptor implements HttpInterceptor {
    constructor (private authService: AuthService) {}

    intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
        const token = this.authService.getToken();
        console.log('Interceptor ejecutandose para:', req.url);
        console.log('Token encontrado:', !!token);
        console.log('Token completo:', token);

        if (token) {
            const authReq = req.clone({
                headers: req.headers.set('Authorization', `Bearer ${token}`)
            });
            console.log('Headers agregados:', authReq.headers.get('Authorization'));
            return next.handle(authReq);
        }
        console.log('No hay token, enviando a sin headers');
        return next.handle(req);
    }
}