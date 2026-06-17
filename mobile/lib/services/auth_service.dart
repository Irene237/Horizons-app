import 'dart:convert';
import 'package:http/http.dart' as http;

class AuthService {
  // Utilise 10.0.2.2 si tu es sur Android Emulator, sinon 127.0.0.1 pour Web/iOS
  final String baseUrl = "http://127.0.0.1:8000/api";

  Future<Map<String, dynamic>?> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );

      // --- LOG DE DÉBOGAGE POUR DIAGNOSTIC ---
      print("Statut du serveur : ${response.statusCode}");
      print("Corps de la réponse : ${response.body}");

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        // On vérifie si la clé 'token' existe réellement
        if (data.containsKey('token')) {
          return data;
        } else {
          print("Erreur : La réponse 200 ne contient pas de token.");
          return null;
        }
      } else {
        // Affiche l'erreur renvoyée par Laravel (souvent des erreurs de validation 422)
        print("Échec de la connexion : ${response.statusCode}");
        return null;
      }
    } catch (e) {
      // Si le serveur est éteint ou l'URL est mauvaise
      print("Erreur réseau/exception : $e");
      return null;
    }
  }
}