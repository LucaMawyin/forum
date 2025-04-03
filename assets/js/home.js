
document.addEventListener('DOMContentLoaded', () => {
  const postsContainer = document.querySelector('.posts-container');
  const coursesContainer = document.querySelector('.communities ul');
  const sortBySelect = document.getElementById('sort-by');
  
  const courses = [
      {
          code: '1XC3',
          name: 'Computer Science Practice & Experience: Development Basics',
          description: 'Introduction to the discipline of computing, software design, and development fundamentals.',
          instructor: 'Dr. Vincent Maccio',
          students: 120
      },
      {
          code: '1DM3',
          name: 'Discrete Mathematics for Computer Science',
          description: 'Sets, functions, relations, trees, graphs, algebras, and their computer representations.',
          instructor: 'Dr. William Smith',
          students: 95
      },
      {
          code: '2C03',
          name: 'Data Structures and Algorithms',
          description: 'Advanced data structures and algorithm design/analysis including sorting, hashing, and graph algorithms.',
          instructor: 'Dr. Jessica Lee',
          students: 85
      },
      {
          code: '3FP3',
          name: 'Functional Programming',
          description: 'Functional programming concepts using languages like Haskell, including recursion, higher-order functions, and type systems.',
          instructor: 'Dr. Robert Johnson',
          students: 65
      }
  ];
  
  const posts = [
      {
          id: 1,
          title: 'Understanding Recursion in Programming',
          content: 'I\'m struggling with understanding how recursive functions work, especially with the call stack. Can someone explain with examples how recursion works in practice?',
          author: 'Alex Johnson',
          authorImage: 'assets/images/user.png',
          course: '1XC3',
          created: '2025-04-01T12:30:00',
          views: 5,
          replies: 3,
          isPinned: false,
          tags: ['recursion', 'functions', 'programming-basics']
      },
      {
          id: 2,
          title: 'How to optimize my binary search algorithm?',
          content: 'I\'ve implemented a binary search but it seems slow on large datasets. Are there any optimizations I should consider?',
          author: 'Sarah Miller',
          authorImage: 'assets/images/user.png',
          course: '2C03',
          created: '2025-03-31T15:45:00',
          views: 31,
          replies: 5,
          isPinned: true,
          tags: ['algorithms', 'binary-search', 'optimization']
      },
      {
          id: 3,
          title: 'Help with set theory problem',
          content: 'I\'m trying to solve this problem involving set operations but am getting confused on the approach. Could someone help me understand how to prove set equivalence?',
          author: 'Michael Chen',
          authorImage: 'assets/images/user.png',
          course: '1DM3',
          created: '2025-03-30T09:15:00',
          views: 28,
          replies: 3,
          isPinned: false,
          tags: ['discrete-math', 'set-theory', 'proofs']
      },
      {
          id: 4,
          title: 'How to implement map and filter in Haskell?',
          content: 'I\'m having trouble understanding how to implement my own versions of map and filter functions in Haskell. Any guidance would be appreciated.',
          author: 'Emma Wilson',
          authorImage: 'assets/images/user.png',
          course: '3FP3',
          created: '2025-03-29T14:20:00',
          views: 19,
          replies: 1,
          isPinned: false,
          tags: ['haskell', 'functional-programming', 'higher-order-functions']
      }
  ];
  
  function initializePage() {
      const urlParams = new URLSearchParams(window.location.search);
      const community = urlParams.get('community');
      
      populateCourses(community);
      
      let filteredPosts = posts;
      if (community) {
          filteredPosts = posts.filter(post => post.course === community);
          
          const courseData = courses.find(course => course.code === community);
          if (courseData) {
              document.title = `CodeForum - ${courseData.code}`;
              
              const communityTitle = document.getElementById('community-title');
              const communityDescription = document.getElementById('community-description');
              
              if (communityTitle) {
                  communityTitle.textContent = `${courseData.code}: ${courseData.name}`;
              }
              
              if (communityDescription) {
                  communityDescription.textContent = courseData.description;
              }
          }
      }
      
      displayPosts(filteredPosts);
      
      setupEventListeners();
  }
  
  function populateCourses(activeCourse) {
      if (!coursesContainer) return;
      
      coursesContainer.innerHTML = '';
      
      courses.forEach(course => {
          const listItem = document.createElement('li');
          const link = document.createElement('a');
          
          link.href = `community.php?community=${course.code}`;
          link.textContent = course.code;
          
          if (activeCourse === course.code) {
              link.classList.add('active');
          }
          
          listItem.appendChild(link);
          coursesContainer.appendChild(listItem);
      });
  }
  
  function displayPosts(postsToDisplay) {
      if (!postsContainer) return;
      
      postsContainer.innerHTML = '';
      
      if (postsToDisplay.length === 0) {
          displayEmptyState();
          return;
      }
      
      const sortValue = sortBySelect ? sortBySelect.value : 'recent';
      const sortedPosts = sortPosts(postsToDisplay, sortValue);
      
      sortedPosts.forEach(post => {
          const postElement = createPostElement(post);
          postsContainer.appendChild(postElement);
      });
  }
  
  function sortPosts(postsToSort, sortBy) {
      const sorted = [...postsToSort];
      
      switch (sortBy) {
          case 'popular':
              sorted.sort((a, b) => b.views - a.views);
              break;
              
          case 'unanswered':
              sorted.sort((a, b) => a.replies - b.replies);
              break;
              
          case 'recent':
          default:
              sorted.sort((a, b) => new Date(b.created) - new Date(a.created));
              break;
      }
      
      return sorted.sort((a, b) => (b.isPinned ? 1 : 0) - (a.isPinned ? 1 : 0));
  }
  
  function createPostElement(post) {
      const postLink = document.createElement('a');
      postLink.href = `post.php?id=${post.id}`;
      postLink.classList.add('post-link');
      
      const relativeTime = getRelativeTimeString(new Date(post.created));
      
      postLink.innerHTML = `
          <div class="post-tile ${post.isPinned ? 'pinned' : ''}">
              <h3>${post.title} ${post.isPinned ? '<span class="pinned-badge">Pinned</span>' : ''}</h3>
              <div class="post-meta">
                  <div class="post-author">
                      <img src="${post.authorImage}" alt="${post.author}'s Avatar">
                      <span>${post.author} • ${relativeTime}</span>
                  </div>
                  <div class="post-stats">
                      <span><i class="fas fa-eye"></i> ${post.views}</span>
                      <span><i class="fas fa-comment"></i> ${post.replies}</span>
                  </div>
              </div>
              <div class="post-content">
                  ${post.content}
              </div>
              <div class="post-tags">
                  ${post.tags.map(tag => `<span class="post-tag">${tag}</span>`).join('')}
              </div>
          </div>
      `;
      
      return postLink;
  }
  
  function displayEmptyState() {
      const emptyState = document.createElement('div');
      emptyState.classList.add('empty-state');
      
      emptyState.innerHTML = `
          <i class="fas fa-comments"></i>
          <h3>No discussions yet</h3>
          <p>Be the first to start a discussion in this community!</p>
          <a href="createPost.php" class="btn btn-primary">Create Post</a>
      `;
      
      postsContainer.appendChild(emptyState);
  }
  
  function setupEventListeners() {
      if (sortBySelect) {
          sortBySelect.addEventListener('change', () => {
              const urlParams = new URLSearchParams(window.location.search);
              const community = urlParams.get('community');
              
              let filteredPosts = posts;
              if (community) {
                  filteredPosts = posts.filter(post => post.course === community);
              }
              
              displayPosts(filteredPosts);
          });
      }
  }
  
  function getRelativeTimeString(date) {
      const now = new Date();
      const diffInSeconds = Math.floor((now - date) / 1000);
      
      if (diffInSeconds < 60) {
          return 'just now';
      }
      
      const diffInMinutes = Math.floor(diffInSeconds / 60);
      if (diffInMinutes < 60) {
          return diffInMinutes === 1 ? '1 minute ago' : `${diffInMinutes} minutes ago`;
      }
      
      const diffInHours = Math.floor(diffInMinutes / 60);
      if (diffInHours < 24) {
          return diffInHours === 1 ? '1 hour ago' : `${diffInHours} hours ago`;
      }
      
      const diffInDays = Math.floor(diffInHours / 24);
      if (diffInDays < 30) {
          return diffInDays === 1 ? '1 day ago' : `${diffInDays} days ago`;
      }
      
      const diffInMonths = Math.floor(diffInDays / 30);
      if (diffInMonths < 12) {
          return diffInMonths === 1 ? '1 month ago' : `${diffInMonths} months ago`;
      }
      
      const diffInYears = Math.floor(diffInMonths / 12);
      return diffInYears === 1 ? '1 year ago' : `${diffInYears} years ago`;
  }
  
  initializePage();
});