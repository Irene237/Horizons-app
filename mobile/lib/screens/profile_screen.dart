import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../services/auth_service.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final AuthService _authService = AuthService();
  Map<String, dynamic>? _userProfile;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');

      final response = await http.get(
        Uri.parse('${_authService.baseUrl}/user'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        setState(() {
          _userProfile = jsonDecode(response.body);
          _isLoading = false;
        });
      } else {
        setState(() => _errorMessage = "Erreur de chargement.");
        _isLoading = false;
      }
    } catch (e) {
      setState(() => _errorMessage = "Erreur de connexion.");
      _isLoading = false;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text("Mon Profil", style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.indigo,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Colors.indigo))
          : _userProfile == null
              ? Center(child: Text(_errorMessage ?? "Erreur"))
              : SingleChildScrollView(
                  child: Column(
                    children: [
                      // En-tête colorée
                      Container(
                        height: 150,
                        width: double.infinity,
                        decoration: const BoxDecoration(
                          color: Colors.indigo,
                          borderRadius: BorderRadius.only(
                            bottomLeft: Radius.circular(30),
                            bottomRight: Radius.circular(30),
                          ),
                        ),
                      ),
                      // Carte de profil
                      Transform.translate(
                        offset: const Offset(0, -70),
                        child: Container(
                          margin: const EdgeInsets.symmetric(horizontal: 20),
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 10, offset: const Offset(0, 5))
                            ],
                          ),
                          child: Column(
                            children: [
                              const CircleAvatar(
                                radius: 50,
                                backgroundColor: Colors.indigoAccent,
                                child: Icon(Icons.person, size: 60, color: Colors.white),
                              ),
                              const SizedBox(height: 15),
                              Text(_userProfile?['name'] ?? 'Inconnu', style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
                              Text(_userProfile?['email'] ?? '', style: TextStyle(color: Colors.grey[600], fontSize: 16)),
                              const Divider(height: 40),
                              ListTile(
                                leading: const Icon(Icons.badge, color: Colors.indigo),
                                title: const Text("Rôle"),
                                subtitle: Text(_userProfile?['role'] ?? 'Utilisateur'),
                              ),
                              const SizedBox(height: 20),
                              SizedBox(
                                width: double.infinity,
                                child: ElevatedButton.icon(
                                  onPressed: () async {
                                    await _authService.logout();
                                    if (mounted) Navigator.pushNamedAndRemoveUntil(context, '/', (route) => false);
                                  },
                                  icon: const Icon(Icons.logout),
                                  label: const Text("Déconnexion"),
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: Colors.redAccent,
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(vertical: 15),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
    );
  }
}