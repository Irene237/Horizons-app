import 'dart:convert';
import 'package:flutter/foundation.dart'; // Import pour kIsWeb
import 'package:http/http.dart' as http;
import '../models/order.dart';

class OrderService {
  // L'adresse change automatiquement selon la plateforme
  String get baseUrl {
    if (kIsWeb) {
      return "http://127.0.0.1:8000/api";
    }
    return "http://10.0.2.2:8000/api";
  }

  Future<List<Order>> getOrders(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/print-orders'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json'
      },
    );

    if (response.statusCode == 200) {
      List body = jsonDecode(response.body);
      return body.map((item) => Order.fromJson(item)).toList();
    } else {
      throw Exception('Erreur ${response.statusCode}');
    }
  }
}