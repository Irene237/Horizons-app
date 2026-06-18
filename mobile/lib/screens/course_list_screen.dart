import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/course.dart';
import '../services/course_service.dart';

class CourseListScreen extends StatefulWidget {
  const CourseListScreen({super.key});

  @override
  State<CourseListScreen> createState() => _CourseListScreenState();
}

class _CourseListScreenState extends State<CourseListScreen> {
  final CourseService _courseService = CourseService();
  late Future<List<Course>> _coursesFuture;
  bool _isEnrolling = false;

  @override
  void initState() {
    super.initState();
    _coursesFuture = _fetchCourses();
  }

  Future<List<Course>> _fetchCourses() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token') ?? '';
    return await _courseService.getCourses(token);
  }

  Future<void> _enroll(int courseId) async {
    if (_isEnrolling) return;

    setState(() => _isEnrolling = true);
    
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token') ?? '';
    
    try {
      await _courseService.enrollInCourse(courseId, token);
      
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Inscription réussie !"), backgroundColor: Colors.green),
      );
      
      setState(() { 
        _coursesFuture = _fetchCourses(); 
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text("Erreur: ${e.toString().replaceAll('Exception: ', '')}"), 
          backgroundColor: Colors.red
        ),
      );
    } finally {
      if (mounted) setState(() => _isEnrolling = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Formations disponibles"),
        actions: [
          IconButton(
            icon: const Icon(Icons.person),
            onPressed: () => Navigator.pushNamed(context, '/profile'),
          ),
        ],
      ),
      body: FutureBuilder<List<Course>>(
        future: _coursesFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(child: Text("Erreur: ${snapshot.error}"));
          }

          final courses = snapshot.data ?? [];
          if (courses.isEmpty) return const Center(child: Text("Aucune formation disponible"));

          return ListView.builder(
            itemCount: courses.length,
            padding: const EdgeInsets.all(10),
            itemBuilder: (context, index) {
              final course = courses[index];
              return Card(
                elevation: 4,
                margin: const EdgeInsets.only(bottom: 15),
                child: Padding(
                  padding: const EdgeInsets.all(15),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(course.title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 5),
                      Text(course.description),
                      const SizedBox(height: 10),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text("Date: ${course.startDate.split('T')[0]}"),
                          Chip(
                            label: Text("${course.availablePlaces} places"),
                            backgroundColor: course.availablePlaces > 0 ? Colors.green[100] : Colors.red[100],
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      // Bouton Inscription
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: (_isEnrolling || course.availablePlaces <= 0) 
                              ? null 
                              : () => _enroll(course.id),
                          child: _isEnrolling 
                              ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                              : const Text("S'INSCRIRE"),
                        ),
                      ),
                      const SizedBox(height: 10),
                      // Bouton Téléchargement remis ici
                      SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          onPressed: () => _courseService.downloadPdf(course.id, 'receipt'),
                          icon: const Icon(Icons.picture_as_pdf),
                          label: const Text("Télécharger Reçu"),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}