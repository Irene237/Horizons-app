import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../services/auth_service.dart';
import '../services/course_service.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final AuthService _authService = AuthService();
  final CourseService _courseService = CourseService();
  
  Map<String, dynamic>? _userProfile;
  List<dynamic> _myEnrollments = [];
  bool _isLoading = true;
  bool _isLoadingEnrollments = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadAllData();
  }

  Future<void> _loadAllData() async {
    await Future.wait([_loadProfile(), _loadEnrollments()]);
  }

  Future<void> _loadProfile() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');
      
      final response = await http.get(
        Uri.parse('${_authService.baseUrl}/user'),
        headers: {
          'Authorization': 'Bearer $token', 
          'Accept': 'application/json'
        },
      );

      if (mounted) {
        setState(() {
          if (response.statusCode == 200) {
            _userProfile = jsonDecode(response.body);
          } else {
            _errorMessage = "Erreur de chargement du profil.";
          }
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _loadEnrollments() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token') ?? '';
      
      final data = await _courseService.getMyEnrollments(token);
      
      if (mounted) {
        setState(() {
          _myEnrollments = data;
          _isLoadingEnrollments = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _isLoadingEnrollments = false);
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
          : _errorMessage != null 
              ? Center(child: Text(_errorMessage!))
              : SingleChildScrollView(
                  child: Column(
                    children: [
                      Container(height: 150, width: double.infinity, color: Colors.indigo),
                      Transform.translate(
                        offset: const Offset(0, -70),
                        child: Column(
                          children: [
                            _buildProfileCard(),
                            const SizedBox(height: 10),
                            _buildEnrollmentsList(),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
    );
  }

  Widget _buildProfileCard() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 10, offset: const Offset(0, 5))],
      ),
      child: Column(
        children: [
          const CircleAvatar(radius: 50, backgroundColor: Colors.indigoAccent, child: Icon(Icons.person, size: 60, color: Colors.white)),
          const SizedBox(height: 15),
          Text(_userProfile?['name'] ?? 'Inconnu', style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
          Text(_userProfile?['email'] ?? '', style: TextStyle(color: Colors.grey[600], fontSize: 16)),
          const Divider(height: 40),
          ListTile(
            leading: const Icon(Icons.badge, color: Colors.indigo),
            title: const Text("Rôle"),
            subtitle: Text(_userProfile?['role'] ?? 'Utilisateur'),
          ),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () async {
                await _authService.logout();
                if (mounted) Navigator.pushNamedAndRemoveUntil(context, '/', (route) => false);
              },
              icon: const Icon(Icons.logout),
              label: const Text("Déconnexion"),
              style: ElevatedButton.styleFrom(backgroundColor: Colors.redAccent, foregroundColor: Colors.white),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEnrollmentsList() {
    if (_isLoadingEnrollments) return const Center(child: CircularProgressIndicator());
    if (_myEnrollments.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text("Mes Formations", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ..._myEnrollments.map((enrollment) => Card(
            margin: const EdgeInsets.symmetric(vertical: 8),
            child: ListTile(
              title: Text(enrollment['course']?['title'] ?? 'Formation'),
              subtitle: Text("Reçu : ${enrollment['receipt_number'] ?? 'N/A'}"),
              trailing: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Facture détaillée
                  IconButton(
                    icon: const Icon(Icons.description, color: Colors.blueGrey), 
                    tooltip: "Facture détaillée",
                    onPressed: () => _courseService.downloadPdf(enrollment['id'], 'invoice'),
                  ),
                  // Reçu simple
                  IconButton(
                    icon: const Icon(Icons.receipt, color: Colors.blue), 
                    tooltip: "Reçu",
                    onPressed: () => _courseService.downloadPdf(enrollment['id'], 'receipt'),
                  ),
                  // Certificat
                  IconButton(
                    icon: const Icon(Icons.picture_as_pdf, color: Colors.indigo),
                    tooltip: "Certificat",
                    onPressed: () => _courseService.downloadPdf(enrollment['id'], 'certificate'),
                  ),
                ],
              ),
            ),
          )),
        ],
      ),
    );
  }
}