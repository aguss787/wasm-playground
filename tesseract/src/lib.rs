extern crate wee_alloc;

// Use `wee_alloc` as the global allocator.
#[cfg(feature="wee")]
#[global_allocator]
static ALLOC: wee_alloc::WeeAlloc = wee_alloc::WeeAlloc::INIT;

#[no_mangle]
fn add_one(x: i32) -> i32 {
    x + 1
}

#[no_mangle]
pub fn sum_all(path: Vec<i32>) -> i32 {
    path.iter().sum()
}

#[no_mangle]
pub fn array_length(path: Vec<i32>) -> i32 {
    path.len() as i32
}

#[repr(C)]
pub struct Path {
    from: i32,
    to: i32,
    cost: i32,
}

#[no_mangle]
pub fn total_path_cost(paths: Vec<Path>) -> i32 {
    paths.iter().map(|i| i.cost).sum()
}

#[no_mangle]
pub fn shortest_path(paths: Vec<Path>, start: i32, finish: i32) -> (i32, Vec<(i32, i32)>) {
    let total = paths.iter().map(|i| i.cost).sum::<i32>() * 2;
    let mut adj_list: HashMap<i32, Vec<(i32, i32)>> = HashMap::new();

    paths.iter().for_each(|i| {
        let list = {
            let c = adj_list.get_mut(&i.from);

            match c {
                None => {
                    drop(c);
                    adj_list.insert(i.from, vec!());
                    adj_list.get_mut(&i.from).unwrap()
                }
                Some(i) => i
            }
        };

        list.push((i.to, i.cost));
    });

    let mut costs = HashMap::new();

    let mut heap = BinaryHeap::new();

    costs.insert(start, (0, None));
    heap.push(Reverse((0, start)));

    while !heap.is_empty() {
        let (current_cost, current_node) = heap.pop().unwrap().0;

        if current_cost != costs.entry(current_node).or_insert((total, None)).0 {
            continue;
        }

        adj_list.get(&current_node).unwrap_or(&vec!())
            .iter()
            .for_each(|(next_node, cost)| {
                let entry = costs.entry(*next_node).or_insert((total, None));

                if current_cost + cost < entry.0 {
                    entry.0 = current_cost + cost;
                    entry.1 = Some(current_node);
                    heap.push(Reverse((current_cost + cost, *next_node)))
                }
            });
    }

    (costs.get(&finish).unwrap_or(&(-1, None)).0, trace_path(&mut costs, finish))
}

fn trace_path(costs: &mut HashMap<i32, (i32, Option<i32>)>, cur: i32) -> Vec<(i32, i32)> {
    trace_path_iter(costs, Some(cur))
        .iter()
        .fold(vec!(), |mut acc: Vec<(i32, i32, i32)>, i| {
            let last = i.1 - acc.last().unwrap_or(&(0,0,0)).1 as i32;
            acc.push((i.0, i.1, last));
            acc
        })
        .iter()
        .map(|i| (i.0, i.2))
        .collect()
}

fn trace_path_iter(costs: &mut HashMap<i32, (i32, Option<i32>)>, cur: Option<i32>) -> Vec<(i32, i32)> {
    match cur {
        None => vec!(),
        Some(node) => {
            let (total, prev) = costs.get(&node).unwrap().clone();

            let mut res = trace_path_iter(costs, prev.clone());
            res.push((node, total.clone()));
            res
        }
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_shortest_path_simple() -> Result<(), String> {
        let paths = vec!(
            Path{from: 1, to: 2, cost: 787}
        );

        assert_eq!(shortest_path(paths, 1, 2).0, 787);

        Ok(())
    }

    #[test]
    fn test_shortest_path_branch() -> Result<(), String> {
        let paths = vec!(
            Path{from: 1, to: 2, cost: 7},
            Path{from: 2, to: 3, cost: 780},
            Path{from: 2, to: 3, cost: 790},
        );

        assert_eq!(shortest_path(paths, 1, 3).0, 787);

        Ok(())
    }

    #[test]
    fn test_shortest_path_branch_path() -> Result<(), String> {
        let paths = vec!(
            Path{from: 1, to: 2, cost: 7},
            Path{from: 2, to: 3, cost: 780},
            Path{from: 2, to: 3, cost: 790},
        );

        assert_eq!(shortest_path(paths, 1, 3).1, vec!((1, 0), (2, 7), (3, 780)));

        Ok(())
    }
}


use std::alloc::{alloc, dealloc, Layout};
use std::mem;
use std::ptr::null_mut;
use std::collections::{HashMap, BinaryHeap};
use std::cmp::Reverse;

#[no_mangle]
pub unsafe extern "C" fn __free(ptr: *mut u8, size: usize) {
    // This happens for zero-length slices, and in that case `ptr` is
    // likely bogus so don't actually send this to the system allocator
    if size == 0 {
        return
    }
    let align = mem::align_of::<usize>();
    let layout = Layout::from_size_align_unchecked(size, align);
    dealloc(ptr, layout);
}

#[no_mangle]
pub extern "C" fn __malloc(size: usize) -> *mut u8 {
    let align = mem::align_of::<usize>();
    if let Ok(layout) = Layout::from_size_align(size, align) {
        unsafe {
            if layout.size() > 0 {
                let ptr = alloc(layout);
                if !ptr.is_null() {
                    return ptr
                }
            } else {
                return align as *mut u8
            }
        }
    }

    return null_mut()
}
