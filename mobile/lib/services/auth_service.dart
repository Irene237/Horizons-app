// mobile/lib/services/auth_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class AuthService {
  final String baseUrl = "http://127.0.0.1:8000/api";

  Future<Map<String, dynamic>?> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {
          'Content-Type': 'application/json', 
          'Accept': 'application/json'
        },
        body: jsonEncode({'email': email, 'password': password}),
      );

      // --- LOG DE DÉBOGAGE CRUCIAL ---
      print("Statut du serveur : ${response.statusCode}");
      print("Réponse brute du serveur : ${response.body}");
      // --------------------------------

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return null;
      }
    } catch (e) {
      print("Exception capturée : $e");
      return null;
    }
  }
}