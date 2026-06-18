import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb; // Ajout pour la détection Web
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'screens/product_list_screen.dart';
import 'screens/order_list_screen.dart';
import 'screens/course_list_screen.dart'; // Import ajouté pour l'Écran 6

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  late Future<Map<String, dynamic>> _statsFuture;

  // URL dynamique pour corriger ton problème de connexion
  String get baseUrl => kIsWeb ? "http://127.0.0.1:8000/api" : "http://10.0.2.2:8000/api";

  @override
  void initState() {
    super.initState();
    _statsFuture = fetchStats();
  }

  Future<Map<String, dynamic>> fetchStats() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');

    final response = await http.get(
      Uri.parse('$baseUrl/dashboard/stats'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body)['data'];
    } else {
      throw Exception('Erreur ${response.statusCode}: ${response.body}');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Tableau de bord")),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _statsFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(child: Text("Erreur : ${snapshot.error}"));
          }

          final data = snapshot.data!;

          return GridView.count(
            padding: const EdgeInsets.all(16),
            crossAxisCount: 2,
            crossAxisSpacing: 16,
            mainAxisSpacing: 16,
            children: [
              _buildStatCard("Ventes jour", "${data['ventes_du_jour']}", Icons.attach_money, null),
              
              _buildStatCard("Commandes", "${data['commandes_impression_attente']}", Icons.shopping_cart, () {
                Navigator.push(context, MaterialPageRoute(builder: (context) => const OrderListScreen()));
              }),
              
              _buildStatCard("Stock Critique", "${data['stock_critique'].length}", Icons.warning, () {
                Navigator.push(context, MaterialPageRoute(builder: (context) => const ProductListScreen()));
              }, color: Colors.red),
              
              // Navigation ajoutée pour les formations
              _buildStatCard("Formations", "${data['apprenants_inscrits_mois']}", Icons.school, () {
                Navigator.push(context, MaterialPageRoute(builder: (context) => const CourseListScreen()));
              }, color: Colors.green),
            ],
          );
        },
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, VoidCallback? onTap, {Color color = Colors.indigo}) {
    return Card(
      elevation: 4,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(4),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 40, color: color),
            const SizedBox(height: 8),
            Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
            Text(value, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
      ),
    );
  }
}