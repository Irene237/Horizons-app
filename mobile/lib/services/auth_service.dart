import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class AuthService {
  // CONFIGURATION AUTOMATIQUE
  String get baseUrl {
    if (kIsWeb) {
      // Pour Chrome, on utilise localhost
      return "http://127.0.0.1:8000/api";
    }
    // Pour téléphone physique, remplace par l'IP de ton PC (ex: 192.168.1.15)
    return "http://192.168.1.XX:8000/api"; 
  }

  Future<Map<String, dynamic>?> login(String email, String password) async {
    try {
      final url = Uri.parse('$baseUrl/login');
      
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );

      // DEBUG : Affiche ce qui se passe réellement
      debugPrint("URL appelée : $url");
      debugPrint("Code statut : ${response.statusCode}");
      debugPrint("Réponse : ${response.body}");

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data.containsKey('token')) {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('auth_token', data['token']);
          
          if (data['client'] != null) {
            await prefs.setInt('client_id', data['client']['id']);
          }
          return data;
        }
      }
      return null;
    } catch (e) {
      debugPrint("Erreur réseau : $e");
      return null;
    }
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');

    try {
      await http.post(
        Uri.parse('$baseUrl/logout'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );
    } catch (e) {
      debugPrint("Erreur logout : $e");
    } finally {
      await prefs.remove('auth_token');
      await prefs.remove('client_id');
    }
  }
}