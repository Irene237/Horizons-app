import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/product.dart';

class ProductService {
  final String baseUrl = "http://127.0.0.1:8000/api";

  Future<List<Product>> getProducts() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');

      final response = await http.get(
        Uri.parse('$baseUrl/products'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        // On décode le corps de la réponse
        final List<dynamic> body = jsonDecode(response.body);
        
        // On transforme chaque élément JSON en objet Product
        return body.map((dynamic item) => Product.fromJson(item)).toList();
      } else {
        // En cas d'erreur serveur (ex: 401, 500), on lance une exception avec le message du serveur
        throw Exception('Erreur ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      // On logue l'erreur pour la voir dans la console VS Code
      print("Erreur dans ProductService: $e");
      throw Exception('Impossible de charger les produits');
    }
  }
}