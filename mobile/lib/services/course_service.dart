import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';
import '../models/course.dart';

class CourseService {
  String get baseUrl {
    if (kIsWeb) {
      return "http://127.0.0.1:8000/api";
    }
    // Utilise 10.0.2.2 pour l'émulateur Android, ou ton IP locale pour un tel physique
    return "http://10.0.2.2:8000/api";
  }

  // 1. Récupération des formations
  Future<List<Course>> getCourses(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/courses'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      List body = jsonDecode(response.body);
      return body.map((item) => Course.fromJson(item)).toList();
    } else {
      throw Exception('Erreur de chargement: ${response.statusCode}');
    }
  }

  // 2. Inscription à une formation
  Future<void> enrollInCourse(int courseId, String token) async {
    final prefs = await SharedPreferences.getInstance();
    final clientId = prefs.getInt('client_id'); 

    if (clientId == null) {
      throw Exception('ID client introuvable, veuillez vous reconnecter.');
    }

    final response = await http.post(
      Uri.parse('$baseUrl/courses/enroll'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'course_id': courseId,
        'client_id': clientId,
        'payment_status': 'Non payé',
        'amount_paid': 0
      }),
    );

    if (response.statusCode != 200 && response.statusCode != 201) {
      final errorData = jsonDecode(response.body);
      throw Exception(errorData['message'] ?? 'Erreur lors de l\'inscription');
    }
  }

  // 3. Récupération des inscriptions de l'utilisateur
  Future<List<dynamic>> getMyEnrollments(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/my-enrollments'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Erreur lors du chargement de vos formations');
    }
  }

  // 4. Téléchargement PDF (Reçu ou Certificat)
  Future<void> downloadPdf(int enrollmentId, String type) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token') ?? '';

    // URL sécurisée avec le token en paramètre
    final url = Uri.parse('$baseUrl/courses/enrollments/$enrollmentId/$type-pdf?token=$token');
    
    // Vérification de la disponibilité du lien
    if (await canLaunchUrl(url)) {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    } else {
      throw Exception('Impossible d\'ouvrir le document PDF');
    }
  }
}